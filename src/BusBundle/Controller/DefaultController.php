<?php

namespace BusBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\HttpFoundation\Response;
use BusBundle\Entity\BusStop;
use Goutte\Client;
use BusBundle\Entity\BusDeparture;
use BusBundle\Entity\BusLine;

set_time_limit(2400);
class DefaultController extends Controller
{
    /**
     * @Route("/")
     */
    public function indexAction()
    {
        return $this->render('BusBundle:Default:index.html.twig');
    }

    /**
     * @Route("/bus-stops", name="bus-stops", options={"expose"=true})
     */
    public function listBusStopsAction()
    {
        $serializer = $this->get('jms_serializer');
        $busStops = $this->getDoctrine()->getManager()->getRepository('BusBundle:BusStop')->findAll();
        $busStops = $serializer->serialize($busStops, 'json');

        return new Response($busStops);
    }

    /**
     * @Route("/bus-stops-get/{id}", name="bus-stops-get", options={"expose"=true})
     */
    public function listBusStopAction($id)
    {
        $serializer = $this->get('jms_serializer');
        $busStop = $this->getDoctrine()->getManager()->getRepository('BusBundle:BusStop')->find($id);
        if (!$busStop) {
            return new JsonResponse('Unauthorized', 400);
        }

        $date = date('w');
        if ($date == 6) {
            $date = BusDeparture::TYPE_SATURDAY;
        } elseif ($date == 7) {
            $date = BusDeparture::TYPE_HOLIDAY;
        } else {
            $date = BusDeparture::TYPE_WEEKDAY;
        }

        $busStopBoard = array(
            'name' => $busStop->getName(),
            'departures' => $this->getDoctrine()->getManager()->getRepository('BusBundle:BusDeparture')->getNextDepartures($id, $date)
        );

        return new Response($serializer->serialize($busStopBoard, 'json'));
    }

    /**
     * @Route("/bus-stops-fetch", name="bus-stops-fetch", options={"expose"=true})
     */
    public function fetchAction()
    {
        $busStops = array();
        $client = new Client();
        $scrapper = $client->request('GET', 'http://rozklady.mpk.krakow.pl/aktualne/przystan.htm');
        $scrapper->filter('li > a')->each(function ($node) use (&$busStops) {
            preg_match("/([^\/]*).htm$/", $node->link()->getUri(), $stops);
            $id = $stops[1];
            $busStops[$id]['name'] = $node->text();
        });

        foreach ($busStops as $key => $busStop)
        {
            $scrapper = $client->request('GET', 'http://rozklady.mpk.krakow.pl/aktualne/p/' . $key . '.htm');
            $scrapper->filter('li > a')->each(function ($node) use (&$busStops, $key) {
                if($node->text() != 'Inne przystanki') {
                    $line = explode(' - > ', $node->text());
                    $url = explode('/', $node->link()->getUri());
                    $url[count($url)-1] = str_replace('r', 't', $url[count($url)-1]);
                    $line[] = implode('/', $url);

                    if ($line[0] > 600 && $line[0] < 615) {
                        $busStops[$key]['lines'][] = $line;
                    } else {
                        unset($busStops[$key]);
                    }
                }
            });
        }

        foreach ($busStops as $key => $busStop)
        {
            $departures = array();
            if(!array_key_exists('lines', $busStop)) {
                $busStop[$key]['lines'] = array();
            }
            foreach ($busStops[$key]['lines'] as $lineId => $line) {
                $scrapper = $client->request('GET', $line[2]);
                $scrapper->filter('.celldepart table tr')->each(function ($node) use (&$busStops, $key, $lineId, &$line, &$departures) {
                    if($node->children()->attr('class') == 'cellday' || $node->children()->attr('class') == 'cellinfo') {
                        return;
                    }

                    $x = 0; $type = 'p'; $hour = 0;
                    foreach ($node->children() as $child) {
                        if ($child->getAttributeNode('class')->value == 'cellhour') {
                            if ($x % 6 == 0) {
                                $type = BusDeparture::TYPE_WEEKDAY;
                            }
                            if ($x % 6 == 2) {
                                $type = BusDeparture::TYPE_SATURDAY;
                            }
                            if ($x % 6 == 4) {
                                $type = BusDeparture::TYPE_HOLIDAY;
                            }
                            $hour = $child->textContent;
                            $x++;
                            continue;
                        }

                        $minutes = explode(' ', $child->textContent);

                        foreach ($minutes as $minute) {
                            $minute = preg_replace('#[a-zA-Z]#', '', $minute);
                            if (!empty($minute) && $minute != '-') {
                                $departures[$type][$hour][] = $minute;
                            }
                        }
                        $x++;
                    }
                });

                $busStops[$key]['lines'][$lineId][2] = $departures;
            }
        }

        foreach ($busStops as $key => $busStop)
        {
            if (!isset($busStop['name'])) {
                continue;
            }
            $stop = $this->getDoctrine()->getManager()->getRepository('BusBundle:BusStop')->findOneBy((array('name' => $busStop['name'])));
            if ($stop == null) {
                $stop = new BusStop();
                $stop->setName($busStop['name']);
                $this->getDoctrine()->getManager()->persist($stop);
            }

            foreach($busStops[$key]['lines'] as $lineId => $busLine) {
                $line = $this->getDoctrine()->getManager()->getRepository('BusBundle:BusLine')->findOneBy((array('name' => $busLine[0], 'endpoint' => $busLine[1])));
                if ($line == null) {
                    $line = new BusLine();
                    $line->setName($busLine[0]);
                    $line->setEndpoint($busLine[1]);
                    $this->getDoctrine()->getManager()->persist($line);
                }

                foreach($busStops[$key]['lines'][$lineId][2] as $type => $busDepartures) {
                    foreach ($busDepartures as $hour => $minutes) {
                        foreach($minutes as $minute) {
                            $departure = new BusDeparture();
                            $departure->setDepartureType($type);
                            $departure->setDepartureTime(new \DateTime('1970-12-12' . $hour.':'.$minute));
                            $departure->setBusLine($line);
                            $departure->setBusStop($stop);
                            $this->getDoctrine()->getManager()->persist($departure);
                        }
                    }
                }

                 $this->getDoctrine()->getManager()->flush();
            }
        }


        return new JsonResponse($busStops);
    }
}
