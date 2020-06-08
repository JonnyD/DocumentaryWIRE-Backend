<?php

namespace App\Controller;

use App\Enum\DocumentaryType;
use App\Object\DTO\Episode;
use App\Object\DTO\Season;
use App\Object\DTO\Series;
use App\Object\OMDb;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;
use PhpParser\Comment\Doc;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations as FOSRest;

class OMDBController extends BaseController implements ClassResourceInterface
{
    /**
     * @FOSRest\Get("/omdb/search", name="get_documentaries_from_omdb", options={ "method_prefix" = false })
     *
     * @param Request $request
     */
    public function searchOMDBAction(Request $request)
    {
        $type = $request->query->get('type');
        $hasType = DocumentaryType::hasType($type);
        if (!$hasType) {
            //@TODO
            throw new \Exception();
        }

        $omdb = new OMDb();
        $omdb->setParams([
            'type' => $type,
            'plot' => 'full',
            'apikey' => $_ENV['OMDB_KEY']
        ]);


        $title = $request->query->get('title');
        if (!isset($title)) {
            return $this->createApiResponse(null, 400);
        }

        $searchedMovies = $omdb->search($title);

        if ($searchedMovies['Response'] == false) {
            $error = $searchedMovies['Error'];
            return $this->createApiResponse($error, 404);
        }

        if ($type != DocumentaryType::EPISODE) {
            $searchedMovies = $searchedMovies['Search'];
        }

        $serialized = [];
        foreach ($searchedMovies as $movie) {
            $serialized[] = $this->serializeSearchResult($movie);
        }

        /**$json = '[
    {
        "title": "Ancient Aliens",
        "year": "2009–",
        "imdbId": "tt1643266",
        "type": "series",
        "poster": "https://m.media-amazon.com/images/M/MV5BMTZkNjYwZjYtZWJiOC00Mjk5LTg4MGUtZWM1NmU4MTE0MjQ0XkEyXkFqcGdeQXVyMzQ5OTk4OQ@@._V1_SX300.jpg"
    },
    {
        "title": "Traveling the Stars: Action Bronson and Friends Watch Ancient Aliens",
        "year": "2016–",
        "imdbId": "tt6022248",
        "type": "series",
        "poster": "https://m.media-amazon.com/images/M/MV5BN2UyYWI0YjUtZjhkOC00NTVmLWEyYzYtNTMxZWViYTlmYWE4XkEyXkFqcGdeQXVyNTgwMTcwNzY@._V1_SX300.jpg"
    },
    {
        "title": "Ancient Aliens: The Mission",
        "year": "2010–",
        "imdbId": "tt3021452",
        "type": "series",
        "poster": null
    }
]';**/
        return $this->createApiResponse($serialized, 200);
    }
    /**
     *
     * @FOSRest\Get("/omdb/{imdbId}", name="get_documentaries_by_id_from_omdb", options={ "method_prefix" = false })
     *
     * @param Request $request
     */
    public function getByImdbIdAction(string $imdbId, Request $request)
    {
        $type = $request->query->get('type');
        $hasType = DocumentaryType::hasType($type);
        if (!$hasType) {
            //@TODO
            throw new \Exception();
        }

        $omdb = new \App\Object\OMDb();
        $omdb->setParams([
            'type' => $type,
            'plot' => 'full',
            'apikey' => $_ENV['OMDB_KEY']
        ]);

        if (!isset($imdbId)) {
            //@TODO

            throw new AccessDeniedException();
        }

        $season = $request->query->get('season');
        $episode = $request->query->get('episode');

        $result = $omdb->get_by_id($imdbId, $season, $episode);
        if ($result['Response'] == false) {
            $error = $result['Error'];
            return $this->createApiResponse($error, 404);
        }

        $typeFromAPI = $result['Type'];
        if ($typeFromAPI === 'movie') {
            $type = DocumentaryType::MOVIE;
        } else if ($typeFromAPI === 'series') {
            $type = DocumentaryType::SERIES;
        } else if ($typeFromAPI == DocumentaryType::EPISODE) {
            $type = DocumentaryType::EPISODE;
        } else {
            return $this->createApiResponse(null, 400);
        }

        if ($type === DocumentaryType::SERIES) {
            $seriesDTO = $this->createSeriesDTO($result, $omdb, $imdbId);
            $result = $this->serializeSeries($seriesDTO);
        } else if ($type === DocumentaryType::MOVIE) {
            $result = $this->serializeMovie($result);
        } else if ($type === DocumentaryType::EPISODE) {
            $result = $this->serializeEpisode($result);
        } else {
            return $this->createApiResponse(null, 400);
        }

        /**$json = '{
    "imdbId": "tt1643266",
    "title": "Ancient Aliens",
    "storyline": "Science and mythology - and how they are the same thing.",
    "poster": "https://m.media-amazon.com/images/M/MV5BMTZkNjYwZjYtZWJiOC00Mjk5LTg4MGUtZWM1NmU4MTE0MjQ0XkEyXkFqcGdeQXVyMzQ5OTk4OQ@@._V1_SX300.jpg",
    "imdbRating": 7.3,
    "imdbVotes": 8513,
    "seasons": [
        {
            "number": 1,
            "episodes": [
                {
                    "number": 0,
                    "title": "Chariots, Gods and Beyond",
                    "year": 2009,
                    "length": 120,
                    "storyline": "According to ancient alien theorists, extraterrestrials with superior knowledge of science and engineering landed on Earth thousands of years ago, sharing their expertise with early civilizations and forever changing the course of human history. But how did this concept develop, and is there any evidence to support it? Ancient alien theory grew out of the centuries-old idea that life exists on other planets, and that humans and extraterrestrials have crossed paths before. The theme of human-alien interaction was thrust into the spotlight in the 1960s, driven by a wave of UFO sightings and popular films like 2001: A Space Odyssey. The space program played no small part in this as well: If mankind could travel to other planets, why couldn\'t extraterrestrials visit Earth? In 1968, the Swiss author Erich von Daniken published Chariots of the Gods?, which became an immediate bestseller. In it, he put forth his hypothesis that, thousands of years ago, space travelers from other planets visited Earth, where they taught humans about technology and influenced ancient religions. He is regarded by many as the father of ancient alien theory, also known as the ancient astronaut theory. Most ancient alien theorists, including von Daniken, point to two types of evidence to support their ideas. The first is ancient religious texts in which humans witness and interact with gods or other heavenly beings who descend from the sky-sometimes in vehicles resembling spaceships-and possess spectacular powers. The second is physical specimens such as artwork depicting alien-like figures and ancient architectural marvels like Stonehenge and the pyramids of Egypt. If aliens visited Earth in the past, could they make an appearance in the future? For ancient alien theorists, the answer is a resounding yes. They believe that, by sharing their views with the world, they can help prepare future generations for the inevitable encounter that awaits them.",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMGViZjU0NDQtNGExZC00ODhiLWFmYWYtZGI2MDRiNjNjNTM3XkEyXkFqcGdeQXVyNjk1ODMzNDY@._V1_SX300.jpg",
                    "imdbId": "tt1418108",
                    "imdbRating": 7.7,
                    "imdbVotes": 535
                },
                {
                    "number": 2,
                    "title": "The Visitors",
                    "year": 2010,
                    "length": 89,
                    "storyline": "From The History Channel : \"If ancient aliens visited Earth, who were they, and where did they come from? Possible historic evidence and beliefs are examined around the world. The Dogon ...",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMjExOTk4MjE4M15BMl5BanBnXkFtZTgwMjUzMTY1MjE@._V1_SX300.jpg",
                    "imdbId": "tt1646280",
                    "imdbRating": 7.7,
                    "imdbVotes": 144
                },
                {
                    "number": 3,
                    "title": "The Mission",
                    "year": 2010,
                    "length": 87,
                    "storyline": "From The History Channel : \"If ancient aliens visited Earth, what was their mission, and is there evidence that points to when they will return? Ancient Sumerian tablets describe an alien ...",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMjc4NzQyODg5NF5BMl5BanBnXkFtZTgwODQzMTY1MjE@._V1_SX300.jpg",
                    "imdbId": "tt1646278",
                    "imdbRating": 7.7,
                    "imdbVotes": 144
                },
                {
                    "number": 4,
                    "title": "Closer Encounters",
                    "year": 2010,
                    "length": 88,
                    "storyline": "From The History Channel : \"Reports of encounters with strange beings and sightings of mysterious objects in the sky have occurred throughout history. A 13th century historical book, Otia Imperialia, includes an account of a creature descending from a flying craft over Bristol, England. The log from Christopher Columbus\' first voyage to America contains a report of strange lights in the sky. Medieval art pieces depict disc-shaped objects floating in the heavens. Sightings of flying cigar-shaped crafts were reported during the Black Plague. And there were even discussions of extraterrestrial life among America\'s Founding Fathers. Could these sightings, coming from every part of the world, from biblical times to present day, be evidence that aliens have been with us all along?\"",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMjA4Mjk4MzA2NF5BMl5BanBnXkFtZTgwNDUzMTY1MjE@._V1_SX300.jpg",
                    "imdbId": "tt1646276",
                    "imdbRating": 7.6,
                    "imdbVotes": 131
                },
                {
                    "number": 5,
                    "title": "The Return",
                    "year": 2010,
                    "length": 89,
                    "storyline": "From The History Channel : \"There is evidence that suggests we experienced 20th century alien contact. In 1942, the Battle of Los Angeles involved the US military and Air Defense allegedly ...",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BNDg2MDM5MjI4NV5BMl5BanBnXkFtZTgwMTUzMTY1MjE@._V1_SX300.jpg",
                    "imdbId": "tt1646279",
                    "imdbRating": 7.6,
                    "imdbVotes": 108
                }
            ]
        },
        {
            "number": 2,
            "episodes": [
                {
                    "number": 1,
                    "title": "Mysterious Places",
                    "year": 2010,
                    "length": 44,
                    "storyline": "From The History Channel : \"Mysterious places can be found around the Earth, and, inexplicably, UFOs are often sited near these mystical areas. Planes and ships unaccountably disappear in ...",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BNDI0NjE4MjA3M15BMl5BanBnXkFtZTgwNTQ1ODQ1MjE@._V1_SX300.jpg",
                    "imdbId": "tt1748473",
                    "imdbRating": 7.9,
                    "imdbVotes": 163
                },
                {
                    "number": 2,
                    "title": "Gods and Aliens",
                    "year": 2010,
                    "length": 45,
                    "storyline": "From The History Channel : \"Myths and legends have long been regarded as fantastic stories that describe powerful gods, mutant giants and fearsome monsters. But why do so many different cultures, separated by vast distances, tell the same stories? Is it possible that myths and legends were really eyewitness accounts of ancient astronauts descending to Earth? Ancient texts are filled with stories of gods interacting with humans, offering wisdom, technology, and even impregnating women. Could the demigods of mythology have been the offspring of alien and human unions? If so, could aliens have supplied the Missing Link that accelerated human evolution, advancing our civilization and making us who we are today?\"",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMTgyMzg5MTY3M15BMl5BanBnXkFtZTgwNTczNzU1MjE@._V1_SX300.jpg",
                    "imdbId": "tt1764793",
                    "imdbRating": 7.6,
                    "imdbVotes": 118
                },
                {
                    "number": 3,
                    "title": "Underwater Worlds",
                    "year": 2010,
                    "length": 44,
                    "storyline": "From The History Channel : \"Ancient underwater cities can be found around the globe, but could these aquatic worlds be the ruins of unknown civilizations--or even proof of extraterrestrial ...",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMTQ2NTgyMDk4Nl5BMl5BanBnXkFtZTgwNDU4OTQ1MjE@._V1_SX300.jpg",
                    "imdbId": "tt1776385",
                    "imdbRating": 8,
                    "imdbVotes": 124
                },
                {
                    "number": 4,
                    "title": "Underground Aliens",
                    "year": 2010,
                    "length": 44,
                    "storyline": "If extraterrestrials influenced human history, can evidence of their existence be found in hidden tunnels and caverns around the world? Could a cave in Ecuador contain metallic books inscribed with secrets of alien technology? Was an ancient underground city in Turkey built with alien help... or as a refuge from an alien attack?",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMjIyMzg5NDM4OF5BMl5BanBnXkFtZTgwNzA2MTY1MjE@._V1_SX300.jpg",
                    "imdbId": "tt1784799",
                    "imdbRating": 7.5,
                    "imdbVotes": 116
                },
                {
                    "number": 5,
                    "title": "Aliens and the Third Reich",
                    "year": 2010,
                    "length": 45,
                    "storyline": "From The History Channel : \"If ancient aliens visited Earth in the remote past, could they have given us advanced technology, past down through human history? And could this technology have...",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BNDI2MDc5OTgxN15BMl5BanBnXkFtZTgwMDE2MTY1MjE@._V1_SX300.jpg",
                    "imdbId": "tt1784798",
                    "imdbRating": 7.5,
                    "imdbVotes": 106
                },
                {
                    "number": 6,
                    "title": "Alien Tech",
                    "year": 2010,
                    "length": 90,
                    "storyline": "From The History Channel : \"Super-heated death rays... High-tech rockets... Powerful sonic weapons... Are these examples of modern day science or could these technologies have originated thousands of years ago? Is it possible that early man possessed scientific knowledge far beyond that of our own century? Ancient texts, folklore and art suggest humans witnessed disc-shaped flying machines and fire-spewing chariots. Could these be accounts of flying saucers and rocket ships? And if so, was advanced technology left here by visitors from the stars? Did mankind\'s quest to unlock the secrets of levitation, anti-gravity and laser technology merely spring from our imaginations or did these ideas come from otherworldly beings?\"",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMTg1MTczNTExOF5BMl5BanBnXkFtZTgwOTU0OTU1MjE@._V1_SX300.jpg",
                    "imdbId": "tt1788498",
                    "imdbRating": 7.3,
                    "imdbVotes": 72
                },
                {
                    "number": 7,
                    "title": "Angels and Aliens",
                    "year": 2010,
                    "length": 45,
                    "storyline": "From The History Channel : \"Biblical texts and ancient lore frequently describe winged creatures carrying messages from the heavens. But are angels merely the product of mankind\'s imagination--or do they really exist? If so, where do they come from? Ancient astronaut theorists suggest that the Bible\'s Old Testament reads like a handbook on extraterrestrial visitations. Accounts of angels can also be found in Islamic and Indian texts. Infinite stories around the globe describe unearthly guardians, entrusted to both observe and protect as well as tales of powerful warriors bringing about everything from plague to peace. Are angels really supernatural beings from heaven, or something more? If so, might angels really be travelers--visiting Earth from distant planets?\"",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMjA4OTcwNDg1Nl5BMl5BanBnXkFtZTgwNDA5NjU1MjE@._V1_SX300.jpg",
                    "imdbId": "tt1798317",
                    "imdbRating": 7.2,
                    "imdbVotes": 95
                },
                {
                    "number": 8,
                    "title": "Unexplained Structures",
                    "year": 2010,
                    "length": 43,
                    "storyline": "From The History Channel : \"If ancient aliens visited Earth, can evidence of their existence be found in the mysterious structures that still stand throughout the world? Inexplicably, megalithic structures found on different continents are strikingly similar, and the cutting and moving of the massive stones used to build these magnificent feats would be a struggle for modern day machinery, let alone ancient man. Ancient astronaut theorists suggest that the standing stones in Carnac, France were used as an ancient GPS system for ancient flying machines. The recently discovered Gobekli Tepe in Turkey, which has been dated back 12,000 years, has finely chiseled pillars that experts describe as a Noah\'s Ark in stone. Is it possible that extraterrestrials assisted primitive man in constructing these unexplained structures? If so, what was the purpose of these grand projects?\"",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMjE3NDc5Mzg4NV5BMl5BanBnXkFtZTgwOTk4NjU1MjE@._V1_SX300.jpg",
                    "imdbId": "tt1798318",
                    "imdbRating": 7.7,
                    "imdbVotes": 112
                },
                {
                    "number": 9,
                    "title": "Alien Devastations",
                    "year": 2010,
                    "length": 45,
                    "storyline": "From The History Channel : \"If ancient aliens visited Earth, were they responsible for catastrophes, wars and other deadly disasters to control the fate of the human race? The story of the Great Flood sent by deities to destroy civilizations exists in many prehistoric cultures. There are ancient descriptions of extraterrestrial battles that caused wide-scale destruction, and even reports of UFOs lurking in the shadows of recent natural disasters. The Book of Revelations and the Dead Sea Scrolls describe a future apocalyptic battle between good and evil that will destroy our world. Are these ancient texts proof that aliens are hostile and planning a violent return? Or might they be our saviors, ensuring our survival as a species during times of devastation?\"",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMjAxMDA2NzkwMV5BMl5BanBnXkFtZTgwMjYzMTU1MjE@._V1_SX300.jpg",
                    "imdbId": "tt1805629",
                    "imdbRating": 7.4,
                    "imdbVotes": 75
                },
                {
                    "number": 10,
                    "title": "Alien Contacts",
                    "year": 2010,
                    "length": 45,
                    "storyline": "From The History Channel : \"Unearthly visitations... Heavenly messengers... Close encounters... Accounts of humans interacting with celestial beings have existed since the beginning of ...",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BNzA1MzgwMDM2NF5BMl5BanBnXkFtZTgwNDUzMTU1MjE@._V1_SX300.jpg",
                    "imdbId": "tt1805630",
                    "imdbRating": 7.5,
                    "imdbVotes": 141
                }
            ]
        },
        {
            "number": 3,
            "episodes": [
                {
                    "number": 1,
                    "title": "Aliens and the Old West",
                    "year": 2011,
                    "length": 45,
                    "storyline": "Ancient Astronaut theorists believe aliens have been visiting earth for thousands of years. If so, might we find evidence of this even in the stories of America\'s Old West? Are these simply...",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMjIyMzc2NDM1M15BMl5BanBnXkFtZTgwNzY3MDY1MjE@._V1_SX300.jpg",
                    "imdbId": "tt1993487",
                    "imdbRating": 7.3,
                    "imdbVotes": 137
                },
                {
                    "number": 2,
                    "title": "Aliens and Monsters",
                    "year": 2011,
                    "length": 44,
                    "storyline": "Hindu scripture describes an enormous flying creature called a Garuda that shook the ground when it landed on Earth. Is it possible that this monster was actually a misinterpreted alien ...",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMjE4NDM2OTk2Ml5BMl5BanBnXkFtZTgwODczODQ1MjE@._V1_SX300.jpg",
                    "imdbId": "tt2000023",
                    "imdbRating": 7.4,
                    "imdbVotes": 128
                },
                {
                    "number": 3,
                    "title": "Aliens and Sacred Places",
                    "year": 2011,
                    "length": 45,
                    "storyline": "Are sacred places the product of man\'s reverence for God--or the result of contact with ancient space travelers? Jerusalem\'s Temple Mount has been called a heavenly gateway. Islam\'s shrine ...",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMjI4NDIzODg2N15BMl5BanBnXkFtZTgwMzM3ODQ1MjE@._V1_SX300.jpg",
                    "imdbId": "tt2030549",
                    "imdbRating": 7.3,
                    "imdbVotes": 119
                },
                {
                    "number": 4,
                    "title": "Aliens and Temples of Gold",
                    "year": 2011,
                    "length": 45,
                    "storyline": "A church in southern France is said to hold the key to alchemy - and a gateway to another part of the universe. Locals in Cusco, Peru believe UFO sightings are connected to lost gold at the...",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMTgzMjk0NzE0MF5BMl5BanBnXkFtZTgwNDQwNTU1MjE@._V1_SX300.jpg",
                    "imdbId": "tt2012690",
                    "imdbRating": 7.7,
                    "imdbVotes": 115
                },
                {
                    "number": 5,
                    "title": "Aliens and Mysterious Rituals",
                    "year": 2011,
                    "length": 45,
                    "storyline": "Throughout the world, ancient rites and rituals are believed to connect humans to another dimension. But just who..or what - are we communicating with? Could today\'s rituals, such as ...",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMjI4MjA2Mzk3M15BMl5BanBnXkFtZTgwNzU3ODQ1MjE@._V1_SX300.jpg",
                    "imdbId": "tt2030548",
                    "imdbRating": 7.2,
                    "imdbVotes": 110
                },
                {
                    "number": 6,
                    "title": "Aliens and Ancient Engineers",
                    "year": 2011,
                    "length": 44,
                    "storyline": "Might the tools and technology of ancient builders have come from distant galaxies? Evidence suggests that an ancient mountaintop fortress in Peru was constructed with laser-like tools... ...",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMTkxNTk4MTE5NF5BMl5BanBnXkFtZTgwODQ3MjY1MjE@._V1_SX300.jpg",
                    "imdbId": "tt2028670",
                    "imdbRating": 7.9,
                    "imdbVotes": 116
                },
                {
                    "number": 7,
                    "title": "Aliens, Plagues and Epidemics",
                    "year": 2011,
                    "length": 45,
                    "storyline": "Scientists are continually challenged by unidentified strains of bacteria with mysterious origins. Could some of our most crippling plagues and epidemics be traced to the darkest voids of ...",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMjMyMjQ4NzMzN15BMl5BanBnXkFtZTgwMTY4OTQ1MjE@._V1_SX300.jpg",
                    "imdbId": "tt2036542",
                    "imdbRating": 7.3,
                    "imdbVotes": 116
                },
                {
                    "number": 8,
                    "title": "Aliens and Lost Worlds",
                    "year": 2011,
                    "length": 45,
                    "storyline": "Mysterious legends and crumbling ruins are all that is left of our planet\'s lost worlds. But could there be proof of ancient alien visitors hidden among the artifacts of civilizations that ...",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMTY1Mzc0NTY0OV5BMl5BanBnXkFtZTgwNjE0MTY1MjE@._V1_SX300.jpg",
                    "imdbId": "tt2060670",
                    "imdbRating": 7.3,
                    "imdbVotes": 117
                },
                {
                    "number": 9,
                    "title": "Aliens and Deadly Weapons",
                    "year": 2011,
                    "length": 45,
                    "storyline": "Iron swords forged in blazing hot fires. Gunpowder with the power to tear apart human flesh. And rockets capable of destroying entire cities. Throughout history, advances in technology have...",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMTgxMjcwMzgwNV5BMl5BanBnXkFtZTgwNDQxODU1MjE@._V1_SX300.jpg",
                    "imdbId": "tt2053594",
                    "imdbRating": 6.9,
                    "imdbVotes": 114
                },
                {
                    "number": 10,
                    "title": "Aliens and Evil Places",
                    "year": 2011,
                    "length": 45,
                    "storyline": "For thousands of years, there have been places around the world considered dangerous to humans. At Australia\'s Black Mountains, local myths speak of ancient serpent gods and hikers ...",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMTkxNDU3NDg3Nl5BMl5BanBnXkFtZTgwNTcxNTU1MjE@._V1_SX300.jpg",
                    "imdbId": "tt2065067",
                    "imdbRating": 7.4,
                    "imdbVotes": 119
                },
                {
                    "number": 11,
                    "title": "Aliens and the Founding Fathers",
                    "year": 2011,
                    "length": 45,
                    "storyline": "What is the meaning behind secret messages found throughout Washington, D.C.? Did America\'s Founding Fathers know something about ancient aliens that the general public did not? And if so, ...",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMTk3MTg1NDM4Ml5BMl5BanBnXkFtZTgwODcxNTU1MjE@._V1_SX300.jpg",
                    "imdbId": "tt2065068",
                    "imdbRating": 6.7,
                    "imdbVotes": 114
                },
                {
                    "number": 12,
                    "title": "Aliens and Deadly Cults",
                    "year": 2011,
                    "length": 44,
                    "storyline": "Mass suicides. Human sacrifices. Unholy rituals. Throughout history, people have claimed to have otherworldly knowledge, and have led followers to commit horrific acts of violence. Are they...",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMjQwMTgwMzg0MF5BMl5BanBnXkFtZTgwNjcxNTU1MjE@._V1_SX300.jpg",
                    "imdbId": "tt2065066",
                    "imdbRating": 6.6,
                    "imdbVotes": 107
                },
                {
                    "number": 13,
                    "title": "Aliens and the Secret Code",
                    "year": 2011,
                    "length": 45,
                    "storyline": "Megalithic monuments linked together by electromagnetic energy...Prehistoric ruins arranged across vast distances in straight lines...And advanced mathematics carved into landmarks more ...",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMTA1MzA0OTc1NjJeQTJeQWpwZ15BbWU4MDc3MTU1NTIx._V1_SX300.jpg",
                    "imdbId": "tt2065069",
                    "imdbRating": 7,
                    "imdbVotes": 107
                },
                {
                    "number": 14,
                    "title": "Aliens and the Undead",
                    "year": 2011,
                    "length": 45,
                    "storyline": "Zombies rising from their graves...Blood sucking vampires damned for all eternity...and humans trapped in a life and death struggle between heaven and hell. For thousands of years, mankind ...",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BNjM1NDA1Njc2MF5BMl5BanBnXkFtZTgwOTYxNTU1MjE@._V1_SX300.jpg",
                    "imdbId": "tt2065070",
                    "imdbRating": 6.4,
                    "imdbVotes": 104
                },
                {
                    "number": 15,
                    "title": "Aliens, Gods and Heroes",
                    "year": 2011,
                    "length": 45,
                    "storyline": "Superhuman strength..Supernatural powers...And the awesome ability to fly. Throughout history, mankind has told incredible tales of gods with extraordinary powers. But are these accounts ...",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMTQxMjQwOTY5NF5BMl5BanBnXkFtZTgwMjczNzU1MjE@._V1_SX300.jpg",
                    "imdbId": "tt2124206",
                    "imdbRating": 7.1,
                    "imdbVotes": 97
                },
                {
                    "number": 16,
                    "title": "Aliens and the Creation of Man",
                    "year": 2011,
                    "length": 45,
                    "storyline": "Why are humans so different from every other species on Earth? Did we evolve from apes - or is our intelligence the result of contact with an otherworldly source? Could unexplained advances...",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMTk0NTIxMzk0OF5BMl5BanBnXkFtZTgwOTcwOTQ1MjE@._V1_SX300.jpg",
                    "imdbId": "tt2108685",
                    "imdbRating": 7.2,
                    "imdbVotes": 108
                }
            ]
        },
        {
            "number": 4,
            "episodes": [
                {
                    "number": 1,
                    "title": "The Mayan Conspiracy",
                    "year": 2012,
                    "length": 45,
                    "storyline": "The Mayan civilization dominated Central America for nearly 2000 years, but by the 9th century A.D., the great Mayan cities were abandoned, and the Mayan people vanished. What could have happened to this advanced culture?",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BOTA5MDUwNjY1M15BMl5BanBnXkFtZTgwNzA3MTU1MjE@._V1_SX300.jpg",
                    "imdbId": "tt2224659",
                    "imdbRating": 7.8,
                    "imdbVotes": 120
                },
                {
                    "number": 2,
                    "title": "The Doomsday Prophecies",
                    "year": 2012,
                    "length": 45,
                    "storyline": "The Maya created the most sophisticated calendar systems in the ancient world, and according to many scholars their Long Count Calendar will come to an end on December 21, 2012. What does this mean for mankind?",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BODU1NTIzMTI3NV5BMl5BanBnXkFtZTgwMjE3MTU1MjE@._V1_SX300.jpg",
                    "imdbId": "tt2224657",
                    "imdbRating": 7.8,
                    "imdbVotes": 107
                },
                {
                    "number": 3,
                    "title": "The Greys",
                    "year": 2012,
                    "length": 45,
                    "storyline": "Who are the beings with big heads and giant eyes that seem to have been visiting our planet for millennia?",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMjI5MTk2MTIwN15BMl5BanBnXkFtZTgwMDcwMDY1MjE@._V1_SX300.jpg",
                    "imdbId": "tt2241801",
                    "imdbRating": 7.5,
                    "imdbVotes": 105
                },
                {
                    "number": 4,
                    "title": "Aliens and Mega-Disasters",
                    "year": 2012,
                    "length": 45,
                    "storyline": "There are numerous historical, religious and mythic accounts of ancient civilizations being wiped out by volcanoes, earthquakes, floods, and meteors. Did god, or nature bring about such disasters?",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMTk4MTM3OTgzN15BMl5BanBnXkFtZTgwNzUxMzU1MjE@._V1_SX300.jpg",
                    "imdbId": "tt2256945",
                    "imdbRating": 7.1,
                    "imdbVotes": 98
                },
                {
                    "number": 5,
                    "title": "The NASA Connection",
                    "year": 2012,
                    "length": 45,
                    "storyline": "NASA scientists, former astronauts and secret government files all point to the conclusion that there has been contact with extraterrestrial life in the past.",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMTg1Njc3MDc1N15BMl5BanBnXkFtZTgwMzIyMjY1MjE@._V1_SX300.jpg",
                    "imdbId": "tt2271909",
                    "imdbRating": 8.1,
                    "imdbVotes": 72
                },
                {
                    "number": 6,
                    "title": "Mystery of Puma Punku",
                    "year": 2012,
                    "length": 90,
                    "storyline": "Erich von Daniken\'s landmark book, Chariots of the Gods, brought attention to the mysterious phenomena of the Nazca Lines. Hundreds of gigantic lines, some in the shape of animals, geometric patterns and even an alien-like being.",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BODg4OTU0NzE0MF5BMl5BanBnXkFtZTgwODQyNTU1MjE@._V1_SX300.jpg",
                    "imdbId": "tt2288271",
                    "imdbRating": 8,
                    "imdbVotes": 57
                },
                {
                    "number": 7,
                    "title": "Aliens and Bigfoot",
                    "year": 2012,
                    "length": 45,
                    "storyline": "In 1980, three young military men were sent into a UK forest to investigate strange lights. They reportedly encountered a spacecraft of unknown origin.",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMTQ3ODU4NjE3OF5BMl5BanBnXkFtZTgwNzA1OTU1MjE@._V1_SX300.jpg",
                    "imdbId": "tt2301379",
                    "imdbRating": 7.1,
                    "imdbVotes": 69
                },
                {
                    "number": 8,
                    "title": "The Da Vinci Conspiracy",
                    "year": 2012,
                    "length": 45,
                    "storyline": "Might an examination of Leonardo Da Vinci\'s masterful paintings, highly technical hand-drawn sketches, and private journals reveal knowledge of otherworldly technology and extraterrestrial beings?",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMTEwMTI5MTE0MTleQTJeQWpwZ15BbWU4MDg3MzA1NTIx._V1_SX300.jpg",
                    "imdbId": "tt2351617",
                    "imdbRating": 7.2,
                    "imdbVotes": 63
                },
                {
                    "number": 9,
                    "title": "The Time Travelers",
                    "year": 2012,
                    "length": 90,
                    "storyline": "Is it possible that sightings of alien beings or UFOs may actually be evidence of time travelers from the future? And might ancient astronauts actually be time travelers from our future?",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMjkzNjg3NTY1MF5BMl5BanBnXkFtZTgwMTAyNjU1MjE@._V1_SX300.jpg",
                    "imdbId": "tt2338519",
                    "imdbRating": 7.6,
                    "imdbVotes": 40
                },
                {
                    "number": 10,
                    "title": "Aliens and Dinosaurs",
                    "year": 2012,
                    "length": 45,
                    "storyline": "Angkor Wat, Cambodia, is the world\'s largest ancient religious temple. Within its megalithic ruins, researches have discovered a depiction of a species of dinosaur--a stegosaurus.",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BNzkwNTUxMjg2MF5BMl5BanBnXkFtZTgwNjQ1NzU1MjE@._V1_SX300.jpg",
                    "imdbId": "tt2372607",
                    "imdbRating": 7.4,
                    "imdbVotes": 77
                },
                {
                    "number": 11,
                    "title": "Secrets of the Pyramids",
                    "year": 2012,
                    "length": 45,
                    "storyline": "For thousands of years, pyramids were the largest structures on Earth. The best known were constructed in Egypt and Central America, but others have been found all around the world, including in China, Africa and Indonesia.",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMjA2MjQzODA0N15BMl5BanBnXkFtZTgwNDE5NDU1MjE@._V1_SX300.jpg",
                    "imdbId": "tt2165302",
                    "imdbRating": 6.7,
                    "imdbVotes": 60
                },
                {
                    "number": 14,
                    "title": "Destination Orion",
                    "year": 2013,
                    "length": 45,
                    "storyline": "Why were ancient civilizations around the world so focused on the Orion constellation? While scientists site many earthly connections, researchers suggest this point in the night sky may have been a so-called Stargate.",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMjA2Mzg2MDgxOV5BMl5BanBnXkFtZTgwODgyOTU1MjE@._V1_SX300.jpg",
                    "imdbId": "tt2603732",
                    "imdbRating": 6.5,
                    "imdbVotes": 49
                }
            ]
        },
        {
            "number": 5,
            "episodes": [
                {
                    "number": 1,
                    "title": "Secrets of the Tombs",
                    "year": 2013,
                    "length": 45,
                    "storyline": "Across the globe, archaeologists have uncovered ancient tombs filled with strange items and writings. Remarkable similarities have been found at each of the sites. How is it possible that the burial chambers of distant cultures were so similar in design?",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMjIxNzM1ODU5MV5BMl5BanBnXkFtZTgwODkzMjU1MjE@._V1_SX300.jpg",
                    "imdbId": "tt2692700",
                    "imdbRating": 6.9,
                    "imdbVotes": 42
                },
                {
                    "number": 2,
                    "title": "Aliens and Cover-Ups",
                    "year": 2012,
                    "length": 45,
                    "storyline": "In 1980, three young military men were sent into a UK forest to investigate strange lights. They reportedly encountered a spacecraft of unknown origin.",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMTA1OTExMTI5NTVeQTJeQWpwZ15BbWU4MDUzNDU1NTIx._V1_SX300.jpg",
                    "imdbId": "tt2253658",
                    "imdbRating": 7.1,
                    "imdbVotes": 66
                },
                {
                    "number": 2,
                    "title": "Prophets and Prophecies",
                    "year": 2013,
                    "length": 90,
                    "storyline": "They were the messengers of divine knowledge and seers who shared visions of the future. For thousands of years, human civilization has been guided by the words and deeds of ancient prophets, but just who - or what - were these prophets communicating with? Might the experiences of history\'s prophets be evidence of communication with a heavenly source - or were seers like Elijah and Joseph Smith actually communicating with extraterrestrial overlords? And if so, are there similar prophets at work today?",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMjA1NjMzNTM5NV5BMl5BanBnXkFtZTgwMDI2MTgwMzE@._V1_SX300.jpg",
                    "imdbId": "tt2792792",
                    "imdbRating": 6.6,
                    "imdbVotes": 43
                },
                {
                    "number": 5,
                    "title": "The Von Däniken Legacy",
                    "year": 2013,
                    "length": 90,
                    "storyline": "A young man\'s spiritual journey. A controversial bestseller. And a scientific theory that threatened to change the world. Just what are the facts behind Erich von Daniken and the Ancient Astronaut theory?",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMTY4ODg5MDI3OV5BMl5BanBnXkFtZTgwNTI3MDY1MjE@._V1_SX300.jpg",
                    "imdbId": "tt2796066",
                    "imdbRating": 6.9,
                    "imdbVotes": 33
                },
                {
                    "number": 6,
                    "title": "Secrets of the Tombs",
                    "year": 2013,
                    "length": 44,
                    "storyline": "Across the globe, archaeologists have uncovered ancient tombs filled with strange items and writings. Remarkable similarities have been found at each of the sites. How is it possible that ...",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BYTA3ZjliNWQtZmZlOS00ODE5LTg0N2EtYjE3NDhhZGI2YzA0XkEyXkFqcGdeQXVyNTM3MDMyMDQ@._V1_SX300.jpg",
                    "imdbId": "tt5995716",
                    "imdbRating": 7.6,
                    "imdbVotes": 26
                },
                {
                    "number": 7,
                    "title": "The Monoliths",
                    "year": 2013,
                    "length": 90,
                    "storyline": "Massive stone structures that reach to the heavens... strange, giant figures buried deep in the Earth... and energy emitting spheres connected around the globe... What compelled ancient man to move colossal stones?",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMzU3MTg0MjUyOV5BMl5BanBnXkFtZTgwOTcyMjY1MjE@._V1_SX300.jpg",
                    "imdbId": "tt2827754",
                    "imdbRating": 6.7,
                    "imdbVotes": 31
                },
                {
                    "number": 8,
                    "title": "Beyond Nazca",
                    "year": 2013,
                    "length": 90,
                    "storyline": null,
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMTkxNTk4MTE5NF5BMl5BanBnXkFtZTgwODQ3MjY1MjE@._V1_SX300.jpg",
                    "imdbId": "tt2802024",
                    "imdbRating": 6.3,
                    "imdbVotes": 34
                },
                {
                    "number": 8,
                    "title": "The Power of Three",
                    "year": 2013,
                    "length": 45,
                    "storyline": "The ancient Egyptian design of the three pyramids of Giza; diverse cultures worship the all-knowing triads; an ancient tablet noted the power of the number three; the language of human DNA is written in a pattern of threes.",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMTYyMTkxNzgyN15BMl5BanBnXkFtZTgwMTkzODU1MjE@._V1_SX300.jpg",
                    "imdbId": "tt3230918",
                    "imdbRating": 6.8,
                    "imdbVotes": 37
                },
                {
                    "number": 9,
                    "title": "Strange Abductions",
                    "year": 2013,
                    "length": 90,
                    "storyline": "The abduction of Travis Walton in 1975; Travis claims to have been abducted by extraterrestrial beings and taken aboard their space ship.",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMjMwMTM1NjI0MF5BMl5BanBnXkFtZTgwOTI0MjY1MjE@._V1_SX300.jpg",
                    "imdbId": "tt2702010",
                    "imdbRating": 7,
                    "imdbVotes": 31
                },
                {
                    "number": 9,
                    "title": "The Crystal Skulls",
                    "year": 2013,
                    "length": 90,
                    "storyline": "Luminous relics in the shape of human skulls. Otherworldly artifacts believed to harness the secrets of the universe. Are the Crystal Skulls part of an elaborate hoax? Or do they possess a great, perhaps even otherworldly power?",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMTUzNzY3NjYxNV5BMl5BanBnXkFtZTgwODc4MDU1MjE@._V1_SX300.jpg",
                    "imdbId": "tt3213756",
                    "imdbRating": 6.2,
                    "imdbVotes": 40
                },
                {
                    "number": 11,
                    "title": "The Viking Gods",
                    "year": 2013,
                    "length": 45,
                    "storyline": "They were teachers and destroyers. Mighty warriors who blazed across the skies in gleaming chariots and wielded magical weapons that could bring down mountains. Odin, Frey and Thor.",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMjAyMjc2NTgyMV5BMl5BanBnXkFtZTgwOTIwMDY1MjE@._V1_SX300.jpg",
                    "imdbId": "tt2831124",
                    "imdbRating": 7,
                    "imdbVotes": 54
                },
                {
                    "number": 11,
                    "title": "Magic of the Gods",
                    "year": 2013,
                    "length": 45,
                    "storyline": "Wondrous wizards... Mysterious sorcerers... And heavenly beings... possessed of unimaginable power. Throughout the ancient world, magic was used for everything from healing to communicating...",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMTQzMDQwOTAzMl5BMl5BanBnXkFtZTgwNzU3MDY1MjE@._V1_SX300.jpg",
                    "imdbId": "tt3281456",
                    "imdbRating": 6.5,
                    "imdbVotes": 39
                },
                {
                    "number": 14,
                    "title": "Emperors, Kings and Pharaohs",
                    "year": 2013,
                    "length": 45,
                    "storyline": "Powerful monarchs ruling by divine right. Centuries-old dynasties amassing enormous wealth. And legendary leaders wielding powerful weapons of mass destruction. Is it possible that ancient rulers were, in fact, connected with divine or, some would say, extraterrestrial forces? And, if so, what was their purpose? Ancient emperors, kings, and pharaohs were often attributed with otherworldly strength, ability, and wisdom; many were even considered gods by the people they ruled. Who were these exceptional leaders, and how did some accomplish so much in their time? Ancient Astronaut theorists believe that these great rulers throughout history were \"chosen\" and guided by extraterrestrials. Is it possible that the world\'s legendary leaders might have shared a common genetic trait? A so-called, \"leadership gene\"? And might this gene have been--not a product of genetic evolution--but inherited by mankind from some otherworldly source?",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMzcxMjUxMDc4NF5BMl5BanBnXkFtZTgwMzMwOTQ1MjE@._V1_SX300.jpg",
                    "imdbId": "tt3306992",
                    "imdbRating": 6.1,
                    "imdbVotes": 34
                },
                {
                    "number": 15,
                    "title": "Mysterious Relics",
                    "year": 2013,
                    "length": 45,
                    "storyline": "Sacred bones... Golden hats... And fragments of sacred objects thought to possess healing properties. All around the world, archaeologists have uncovered mysterious relics revered by ancient cultures for thousands of years. Now on display in museums, temples and churches, millions of people travel great distances just to be in their presence. What is it about these ancient objects that draws people to them? Could the truth of mankind\'s origins be found in the fabric, metal, stone and bones of our distant past? And could these mysterious relics really provide us with the ability to access divine or otherworldly power?",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BODExNzUzOTQ5Ml5BMl5BanBnXkFtZTgwMzIyMDU1MjE@._V1_SX300.jpg",
                    "imdbId": "tt3338974",
                    "imdbRating": 6.5,
                    "imdbVotes": 33
                }
            ]
        },
        {
            "number": 6,
            "episodes": [
                {
                    "number": 3,
                    "title": "The Anunnaki Connection",
                    "year": 2013,
                    "length": 90,
                    "storyline": null,
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMTkxNTk4MTE5NF5BMl5BanBnXkFtZTgwODQ3MjY1MjE@._V1_SX300.jpg",
                    "imdbId": "tt3270832",
                    "imdbRating": 7,
                    "imdbVotes": 29
                },
                {
                    "number": 4,
                    "title": "Aliens and Stargates",
                    "year": 2014,
                    "length": 90,
                    "storyline": "Legends from around the world speak of sacred entryways to the land of the gods. Gates that allowed instant passage not just beyond the confines of Earth, but beyond space and time. Are ...",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMjA1NjMzNTM5NV5BMl5BanBnXkFtZTgwMDI2MTgwMzE@._V1_SX300.jpg",
                    "imdbId": "tt3501750",
                    "imdbRating": 6.5,
                    "imdbVotes": 24
                },
                {
                    "number": 5,
                    "title": "Aliens in America",
                    "year": 2014,
                    "length": 90,
                    "storyline": "Strange rock carvings from 8,000 B.C. Unexplained sightings in the new world. And a frightening anomaly plaguing the American West. Is there more to America\'s past than is found in our history books? Could the mysterious stories and relics found throughout the United States be evidence of extraterrestrial contact in the distant past? In 1909, the Phoenix Gazette reported the finding of a strange man-made cave filled with ancient objects and mummies. Could there be a connection between the mysterious cave and the legend of Native American ancestors called \"Star Beings?\" Strange lights and unexplained weather anomalies were documented throughout colonial America. Is there evidence that early American settlers had close encounters with extraterrestrial entities? Could UFO sightings and reports of alien contact be part of America\'s untold history?",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMTc4NTkzNDA2OF5BMl5BanBnXkFtZTgwMDY4OTE1NTE@._V1_SX300.jpg",
                    "imdbId": "tt3533508",
                    "imdbRating": 6.4,
                    "imdbVotes": 22
                },
                {
                    "number": 5,
                    "title": "The Satan Conspiracy",
                    "year": 2013,
                    "length": 45,
                    "storyline": "He is the evil one, the Prince of Darkness, the ruler of Hell. Satan conjures up horrifying images of a horned beast whose sole purpose is to destroy humanity. Yet there is evidence that the fallen angel we know as Satan may be misunderstood.",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMTYyMzg1NTg0N15BMl5BanBnXkFtZTgwOTM2MjU1MjE@._V1_SX300.jpg",
                    "imdbId": "tt3296598",
                    "imdbRating": 6.7,
                    "imdbVotes": 47
                },
                {
                    "number": 6,
                    "title": "Alien Operations",
                    "year": 2013,
                    "length": 45,
                    "storyline": "Mysterious surgeries performed by early humans... Strange beings with miraculous powers... And a cover up--destroying centuries of scientific knowledge. Are recent breakthroughs in medicine the result of years of research?",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BNzA5MDM3ODI5NV5BMl5BanBnXkFtZTgwNDI1MjY1MjE@._V1_SX300.jpg",
                    "imdbId": "tt3312154",
                    "imdbRating": 6.5,
                    "imdbVotes": 48
                },
                {
                    "number": 6,
                    "title": "The Star Children",
                    "year": 2014,
                    "length": 45,
                    "storyline": "On every continent, there are stories of children who \"stand out\" for their strange abilities and advanced knowledge. They are said to have knowledge of things they shouldn\'t or couldn\'t know. The ancient Greeks, Egyptians and many other cultures have legends about exceptional children who are the offspring of humans and the gods. Could the stories be more than mere myth? Is it possible there are children being born on Earth today who can access their alien DNA--a connection that endows them with special gifts and the power to transform society?",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMTY3NTg4MTQ2NV5BMl5BanBnXkFtZTgwMDg4MDU1MjE@._V1_SX300.jpg",
                    "imdbId": "tt3495370",
                    "imdbRating": 6.8,
                    "imdbVotes": 31
                },
                {
                    "number": 7,
                    "title": "Treasures of the Gods",
                    "year": 2014,
                    "length": 45,
                    "storyline": "Sealed vaults possessing a strange and deadly curse, mysterious relics believed to be under otherworldly protection, and riches so priceless, it has cost men their lives. On every continent there are stories of incredible treasures that have been lost for centuries. Is it possible that the world\'s greatest ancient relics are hidden on purpose--and protected by an extraterrestrial source?",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMTU1ODE3NjI4OF5BMl5BanBnXkFtZTgwMTIzNTU1MjE@._V1_SX300.jpg",
                    "imdbId": "tt3510122",
                    "imdbRating": 6.7,
                    "imdbVotes": 30
                },
                {
                    "number": 9,
                    "title": "Aliens and Forbidden Islands",
                    "year": 2013,
                    "length": 44,
                    "storyline": "Legends of lost and mysterious islands, advanced civilizations and Eden-like paradises have been told by sailors throughout human history. Is it possible these stories are more than just the myths of imaginative seafarers? Might evidence of our Ancient Alien origins be found by exploring the world\'s most remote islands--strategic spots where extraterrestrials may have once made their earthly homes... and possibly still visit today? Unidentified submersible objects going at lightning speed... strange crafts coming out of water near remote volcanic islands... and mystifying megalithic ruins in the middle of the ocean. Is it possible UFO activity could be hidden deep underwater and off the coasts of islands? Could this explain why so many islands around the world have stories of unexplained sightings of objects both overhead and beneath the sea going back centuries?",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMTQzNTgwOTYyNV5BMl5BanBnXkFtZTgwNjkwOTcxMzE@._V1_SX300.jpg",
                    "imdbId": "tt3346514",
                    "imdbRating": 7.2,
                    "imdbVotes": 45
                },
                {
                    "number": 10,
                    "title": "Aliens and Insects",
                    "year": 2014,
                    "length": 45,
                    "storyline": "Earth is home to 10 quintillion insects. In cultures throughout the world, insects have been honored, feared and even revered as gods. But might these strange creatures that have inhabited Earth for hundreds of millions of years provide a link to extraterrestrial beings? Is it possible the ancient Egyptians encountered alien beings that had an insect-like appearance? Are the scorpion-tailed locust described in the Book of Revelations some type of advanced weaponry wielded by an extraterrestrial being? Might the Ant People that appear in both Greek and Native American mythology really be grey aliens? Is it possible that there is a connection between insects and aliens? Are insects related to beings from beyond our world, as some Ancient Astronaut theorists suggest? And if so, might they have a secret purpose we have yet to discover?",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMTcyOTMyNDg0NV5BMl5BanBnXkFtZTgwOTA3MjY1MjE@._V1_SX300.jpg",
                    "imdbId": "tt3558716",
                    "imdbRating": 6.8,
                    "imdbVotes": 25
                },
                {
                    "number": 10,
                    "title": "Aliens and the Lost Ark",
                    "year": 2013,
                    "length": 90,
                    "storyline": "The Ark of the Covenant is one of the most sought after religious relics of all times and far more than just a box that contained the Ten Commandments. The biblical stories surrounding the Ark speak of a device with divine powers that was able to produce food, take down stonewalls, kill those that come in contact with it, and provide direct communication to God. Are these stories mere myth? Or did the Ark of the Covenant possess extraordinary powers? What happened to this incredible relic? Could it still be hidden? Are we getting close to a rediscovery--and reactivation--of the Ark? And if so, will the Ark of the Covenant reveal a long, lost connection to our extraterrestrial past?",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMTgyMDE5OTE0NV5BMl5BanBnXkFtZTgwOTY5ODU1MjE@._V1_SX300.jpg",
                    "imdbId": "tt3374512",
                    "imdbRating": 6.6,
                    "imdbVotes": 21
                },
                {
                    "number": 11,
                    "title": "Aliens and Mysterious Mountains",
                    "year": 2013,
                    "length": 45,
                    "storyline": "Across ancient civilizations, the world\'s mountains were viewed as the sacred home to all-powerful gods. Why would so many different cultures share this belief that mountains had a connection to otherworldly beings? The Greeks believed their most powerful gods, the Olympians, waged an epic war for control of the universe from Mt. Olympus. Ruins of a 4000-year-old observatory discovered on a mountain in Macedonia reveal that the ancients tracked celestial movements from on high with remarkable precision. And in Peru, the descendants of the Incans continue an ancient pilgrimage tradition in order to commune with mountain spirits known as Apu. Could these gods have been extraterrestrials? Did they use remote mountain peaks to interact with early man? Might this explain why humans have been drawn to mountains for thousands of years?",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMTQyODA0NTAzN15BMl5BanBnXkFtZTgwOTk5ODU1MjE@._V1_SX300.jpg",
                    "imdbId": "tt3374514",
                    "imdbRating": 7,
                    "imdbVotes": 43
                },
                {
                    "number": 11,
                    "title": "Alien Breeders",
                    "year": 2014,
         *
                    "length": 45,
                    "storyline": "Demonic seduction... hybrid offspring... and strange abductions. In cultures throughout the world, there are tales of intimate encounters with otherworldly beings. But could these stories be more than just mythology? Scientific deconstruction of human genetic makeup has revealed that our origins are not what we once believed. Biologists have determined that early humans mated with several other species of intelligent hominids. Neanderthals, Denisovans, and a third--as of yet unidentified--species may also be represented in our DNA. Is it possible that tales spanning from fallen angels to Greek gods, medieval incubus and succubus to today\'s extraterrestrial sexual encounters are all one in the same? And if so, is it all part of a greater agenda? Might examining these experiences provide not only evidence of extraterrestrial sexual intervention in the remote past, but also reveal the true nature of mankind\'s origins?",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMjAzNzAzMTc1Nl5BMl5BanBnXkFtZTgwOTA3MjU1MjE@._V1_SX300.jpg",
                    "imdbId": "tt3573654",
                    "imdbRating": 7.3,
                    "imdbVotes": 23
                },
                {
                    "number": 19,
                    "title": "Aliens and Superheroes",
                    "year": 2014,
                    "length": 90,
                    "storyline": "From the beginning of recorded history, humans have told stories about beings with super-human strength, super-sonic speed, and supernatural abilities. The ancients had heroic tales about Zeus, Thor, and Hanuman while today we have superhero stories about Superman, Batman, and Spider-man. Mythologists say these epic stories resemble each other because they may have all come from a common set of oral legends created eons ago by our earliest ancestors. But might there be another, more otherworldly reason that the world\'s heroic myths are so similar? Is it possible, as many Ancient Astronaut Theorists contend, that these stories are actually based on extraordinary-- and possibly extraterrestrial--beings that lived on Earth in the distant past?",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BNjQxMDUwMTQ3NF5BMl5BanBnXkFtZTgwNzE1Mjg5MjE@._V1_SX300.jpg",
                    "imdbId": "tt3921386",
                    "imdbRating": 6.8,
                    "imdbVotes": 20
                },
                {
                    "number": 21,
                    "title": "Mysterious Structures",
                    "year": 2014,
                    "length": 90,
                    "storyline": null,
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMTkxNTk4MTE5NF5BMl5BanBnXkFtZTgwODQ3MjY1MjE@._V1_SX300.jpg",
                    "imdbId": "tt3921374",
                    "imdbRating": 6.6,
                    "imdbVotes": 7
                },
                {
                    "number": 22,
                    "title": "Mysterious Devices",
                    "year": 2014,
                    "length": 90,
                    "storyline": null,
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMTkxNTk4MTE5NF5BMl5BanBnXkFtZTgwODQ3MjY1MjE@._V1_SX300.jpg",
                    "imdbId": "tt3921380",
                    "imdbRating": 6.8,
                    "imdbVotes": 5
                }
            ]
        },
        {
            "number": 7,
            "episodes": [
                {
                    "number": 1,
                    "title": "Forbidden Caves",
                    "year": 2014,
                    "length": 90,
                    "storyline": null,
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMjA1NjMzNTM5NV5BMl5BanBnXkFtZTgwMDI2MTgwMzE@._V1_SX300.jpg",
                    "imdbId": "tt4744106",
                    "imdbRating": null,
                    "imdbVotes": null
                },
                {
                    "number": 3,
                    "title": "The God Particle",
                    "year": 2014,
                    "length": 90,
                    "storyline": "It has been called the key to the universe and possibly the most important scientific breakthrough of all time. Could the so-called \"God Particle\" reveal the truth about our origins? And might clues to its significance have been left here on Earth thousands of years ago by extraterrestrial beings? The age-old question, \"where did we come from?\" remains a mystery, but in 2012, scientists at CERN laboratory announced the discovery of the \"God Particle\"--the subatomic particle that is believed to give mass to matter--and offers a closer understanding of just how the universe began. The discovery seemingly pitted science against religion. But could science and religion actually be kindred spirits? Many ancient religions and mythologies describe the origins of the universe in ways very similar to today\'s scientific explanations and proclaim that this information was given to them by otherworldy beings.",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMTQ1NjQyNDY2M15BMl5BanBnXkFtZTgwOTkzMjg5MjE@._V1_SX300.jpg",
                    "imdbId": "tt3900672",
                    "imdbRating": 6.1,
                    "imdbVotes": 18
                },
                {
                    "number": 5,
                    "title": "Aliens and the Red Planet",
                    "year": 2014,
                    "length": 45,
                    "storyline": "Throughout human history, the planet Mars has captured our imagination. Ancient Egyptian astronomers were the first to record the red planet on a chart of the cosmos known as the Senenmut ...",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMTY5MTkwMzU0NV5BMl5BanBnXkFtZTgwNzMyMTY1MjE@._V1_SX300.jpg",
                    "imdbId": "tt3524950",
                    "imdbRating": 7.6,
                    "imdbVotes": 31
                },
                {
                    "number": 6,
                    "title": "The Shamans",
                    "year": 2014,
                    "length": 45,
                    "storyline": "Throughout history, spiritual leaders known as shamans have healed, protected and advised their people. But are they simply putting on elaborate ceremonies? Or could they be in contact with extraterrestrial realms? The Mongol army crushed their enemies and built a massive empire with the help of powerful shamans who could manipulate the weather. Amazonian shamans believe otherworldly beings taught them how to create a powerful brew that allows them to travel beyond Earth to gain knowledge. And on the desert plains of the Kalahari, the San Bushmen practice elaborate trance dances that unlock supernatural capabilities in their healers. Can shamans really communicate with otherworldly beings? Is it possible that through these spiritual mediators, extraterrestrials were able to influence mankind in the distant past? And could such celestial communication continue even today?",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMTYxNjk2MDc2N15BMl5BanBnXkFtZTgwNDc1NDU1MjE@._V1_SX300.jpg",
                    "imdbId": "tt3542890",
                    "imdbRating": 7.3,
                    "imdbVotes": 35
                },
                {
                    "number": 7,
                    "title": "Alien Messages",
                    "year": 2014,
                    "length": 90,
                    "storyline": "There are those who believe that embedded in our most sacred religious texts, as well as in the design and location of ancient monuments, are secret message - messages that may reveal the purpose behind our very existence. Could it be true? Strange and cryptic communications with unknown meaning have been discovered all across the globe throughout every known civilization. Might the key to unlocking the mysteries of the universe be found hidden in numerological, geometrical, and geological puzzles left for us to solve by extraterrestrial being that came to Earth thousands of years ago? Are humans being tested? Have we learned enough to crack the codes necessary to decipher these messages? And if so, might our understanding of them allow us to finally be reunited with our celestial ancestors?",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMjA1NjMzNTM5NV5BMl5BanBnXkFtZTgwMDI2MTgwMzE@._V1_SX300.jpg",
                    "imdbId": "tt4744114",
                    "imdbRating": 6.6,
                    "imdbVotes": 9
                },
                {
                    "number": 10,
                    "title": "Hidden Pyramids",
                    "year": 2015,
                    "length": 90,
                    "storyline": "With infrared satellite technology detecting pyramids hidden underneath the sand in Egypt and airborne laser sensors discovering long lost pyramids beneath the jungles of Central America, many believe we have entered a new pyramid era-an age of discovery that will be marked by unimaginable new findings. 21st century excavations are also revealing fascinating new facts about pyramids that could potentially rewrite history. Archaeologists now believe the ancient pyramids in Peru are older than the Egyptian pyramids, and a recently surveyed megalithic site in Indonesia is now suspected to be a step pyramid built by a long lost civilization 20,000 years ago. But what can explain ancient man\'s apparent obsession with building pyramids? Ancient Astronaut theorists believe the world pyramid phenomena is not a coincidence, but evidence that Earth\'s earliest inhabitants may have had help from extraterrestrials.",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMjA1NjMzNTM5NV5BMl5BanBnXkFtZTgwMDI2MTgwMzE@._V1_SX300.jpg",
                    "imdbId": "tt4744120",
                    "imdbRating": 7.3,
                    "imdbVotes": 19
                },
                {
                    "number": 11,
                    "title": "The Vanishings",
                    "year": 2015,
                    "length": 44,
                    "storyline": "Stories of civilizations large and small disappearing without a trace can be found throughout history and across the globe. From a previously unknown civilization in China, a vanished race of giants, to the unexplained disappearances of the Classic Maya and the Anasazi--elements of these disappearances cannot be completely explained by conventional scholars. Although these cultures have vanished from the face of the Earth, they have left behind intriguing clues--clues, some say point to an otherworldly explanation. And if entire groups have been taken off planet, might there be a connection with sites connected to mysterious vanishings of people, planes, and boats in various locations throughout the world? Could examining these disappearances lead us to not only uncover evidence of extraterrestrial intervention in our remote past, but also reveal the consequences for the future of the human race?",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMTg3NjA4NzI5MV5BMl5BanBnXkFtZTgwMzk2MDI1NTE@._V1_SX300.jpg",
                    "imdbId": "tt4298906",
                    "imdbRating": 7.6,
                    "imdbVotes": 18
                },
                {
                    "number": 12,
                    "title": "The Alien Agenda",
                    "year": 2015,
                    "length": 44,
                    "storyline": "Many of humanity\'s most significant turning points have bore witness to unexplainable events. Is this merely coincidence, or could it be evidence that the course of human history is being manipulated by an alien agenda? Ancient Astronaut theorists have long proposed that extraterrestrials played a pivotal role in our past, and some believe they continue to keep a careful watch over the progress of mankind. Even high-level government officials have made claims that extraterrestrials are visiting our planet with an interest in our technological advancements, including former Canadian Defense Minister Paul Hellyer, who stated that that the cosmos are teaming with life. Could the UFO phenomenon be real? Have extraterrestrials been monitoring human progress for thousands of years--at times helping us to advance, and at other times holding us back?",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMjExMzg3MDQ5NV5BMl5BanBnXkFtZTgwMDA3NTI3NTE@._V1_SX300.jpg",
                    "imdbId": "tt4619044",
                    "imdbRating": 8.2,
                    "imdbVotes": 27
                }
            ]
        },
        {
            "number": 8,
            "episodes": [
                {
                    "number": 1,
                    "title": "Alien Transports",
                    "year": 2014,
                    "length": 90,
                    "storyline": "From ancient accounts of fiery chariots in the sky in the Bible and flying carpets in Ethiopian texts to fire-breathing dragons in Chinese mythology, a look is taken into the possibility of the phenomena being extraterrestrial.",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMjA1NjMzNTM5NV5BMl5BanBnXkFtZTgwMDI2MTgwMzE@._V1_SX300.jpg",
                    "imdbId": "tt3921372",
                    "imdbRating": 7.2,
                    "imdbVotes": 25
                },
                {
                    "number": 4,
                    "title": "Dark Forces",
                    "year": 2015,
                    "length": 44,
                    "storyline": "Good and Evil. Light and Dark. God and Satan. In nearly every culture there are stories of opposing forces at work here on Earth--forces of a supernatural or otherworldly nature.",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMTQ0MDI1OTA4NV5BMl5BanBnXkFtZTgwMzUyMjY1NjE@._V1_SX300.jpg",
                    "imdbId": "tt4909874",
                    "imdbRating": 8,
                    "imdbVotes": 21
                },
                {
                    "number": 5,
                    "title": "The Reptilians",
                    "year": 2014,
                    "length": 44,
                    "storyline": "The Reptilian alien is a fixture of science-fiction, from H.P. Lovecraft\'s tales of Valusians to the Cardassians in Star Trek, to the Visitors of the television series V. But could ancient myths about reptilian creatures provide evidence that they are more than just a pop-culture creation? Legends of serpent beings can be found on every continent. The Bible, the Quran and the ancient texts known as the Nag Hammadi codices all describe reptilian entities interacting with humans. In Central and South America people worship the feathered serpent god called Kulkukan or Quetzalcoatl. In India, the Nagas are half-human half-reptile gods who live underground in a place called Patala. And in China and Japan, many emperors claim to be the descendants of dragons. Could these stories represent real Reptilian beings that people all over the world actually encountered in the ancient past?",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMjE2MjEwOTk0N15BMl5BanBnXkFtZTgwOTk3MTg5MjE@._V1_SX300.jpg",
                    "imdbId": "tt3868156",
                    "imdbRating": 7.3,
                    "imdbVotes": 48
                },
                {
                    "number": 6,
                    "title": "The Tesla Experiment",
                    "year": 2014,
                    "length": 44,
                    "storyline": "He is one of the most prolific inventors of the 20th century--and the man that actually electrified the world. Nikola Tesla shepherded mankind to a new industrial era and laid the groundwork for today\'s technological age--but could that have been by some otherworldly design? A man shrouded in mystery and intrigue, Tesla would speak about obtaining insights in a flash of genius, how he could visualize his inventions in full-detail and manipulate them in his mind, and his compulsion to invent the future. But he also claimed to have direct communication from intelligent beings on other planets. Is it possible that this alleged mad scientist, so far ahead of his time, was actually a human \"receiver\"? A being tapped to advance mankind--and pave the way for a future reunion with our alien ancestors?",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMTQyMjE3ODMyM15BMl5BanBnXkFtZTgwMDA2MTg5MjE@._V1_SX300.jpg",
                    "imdbId": "tt3886306",
                    "imdbRating": 7.8,
                    "imdbVotes": 55
                },
                {
                    "number": 6,
                    "title": "The Other Earth",
                    "year": 2015,
                    "length": 43,
                    "storyline": "Billions of dollars have been spent in our quest to find an \"other Earth.\" Since the launch of the Kepler Telescope in 2009, scientists have identified thousands of planets capable of supporting life. Is it possible that this discovery finally answers the age-old question: \"Are we alone in the universe?\"",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BNTYwMTAyNTgxOV5BMl5BanBnXkFtZTgwNTY3MTA3NjE@._V1_SX300.jpg",
                    "imdbId": "tt4946556",
                    "imdbRating": 7.6,
                    "imdbVotes": 18
                },
                {
                    "number": 10,
                    "title": "The Alien Wars",
                    "year": 2015,
                    "length": 43,
                    "storyline": null,
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMjQxMTA1NTI0NF5BMl5BanBnXkFtZTgwNzI1Mzc5NjE@._V1_SX300.jpg",
                    "imdbId": "tt5024780",
                    "imdbRating": 7.9,
                    "imdbVotes": 19
                }
            ]
        },
        {
            "number": 9,
            "episodes": [
                {
                    "number": 2,
                    "title": "Mysteries of the Sphinx",
                    "year": 2014,
                    "length": 44,
                    "storyline": "The Great Sphinx in Giza is the largest and most studied monolithic sculpture on Earth, yet it remains one of humanities greatest mysteries. This ancient monolith defies explanation, ...",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMTExOTEzMDMyOTJeQTJeQWpwZ15BbWU4MDU2NzI0NDMx._V1_SX300.jpg",
                    "imdbId": "tt4148614",
                    "imdbRating": 7.9,
                    "imdbVotes": 58
                },
                {
                    "number": 3,
                    "title": "Aliens Among Us",
                    "year": 2014,
                    "length": 44,
                    "storyline": "There are those who believe that the evolution of technology is not entirely of our own making. Ancient Astronaut theorists contend that it is by extraterrestrial design that we have become...",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMTk1NzE5MDQ5M15BMl5BanBnXkFtZTgwMTU5NTU4NDE@._V1_SX300.jpg",
                    "imdbId": "tt4165950",
                    "imdbRating": 7.2,
                    "imdbVotes": 59
                },
                {
                    "number": 4,
                    "title": "The Genius Factor",
                    "year": 2014,
                    "length": 44,
                    "storyline": "Throughout history, many of the world\'s greatest thinkers have helped push civilization forward with their profound insights and extraordinary abilities. But the majority of these master ...",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMTg4NTg4MDUzNV5BMl5BanBnXkFtZTgwNTM5MzU4NDE@._V1_SX300.jpg",
                    "imdbId": "tt4415268",
                    "imdbRating": 7.6,
                    "imdbVotes": 52
                },
                {
                    "number": 5,
                    "title": "Secrets of the Mummies",
                    "year": 2014,
                    "length": 44,
                    "storyline": "For thousands of years, in cultures throughout the world, people have practiced elaborate techniques to preserve the human body after death. The ancient Egyptians believed mummification ...",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMjE2MzkwMjkxMl5BMl5BanBnXkFtZTgwNjcxMzY4NDE@._V1_SX300.jpg",
                    "imdbId": "tt4415274",
                    "imdbRating": 7.1,
                    "imdbVotes": 59
                },
                {
                    "number": 6,
                    "title": "Alien Resurrections",
                    "year": 2014,
                    "length": 44,
                    "storyline": "Throughout history, incredible stories have been told of the dead rising from their graves, mummies journeying into the afterlife, and people on the brink of death mingling with deceased ...",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMjE5NDY2NTk4MV5BMl5BanBnXkFtZTgwMTcyODI2MzE@._V1_SX300.jpg",
                    "imdbId": "tt4221646",
                    "imdbRating": 7.3,
                    "imdbVotes": 50
                },
                {
                    "number": 7,
                    "title": "Alien Messages",
                    "year": 2015,
                    "length": 90,
                    "storyline": null,
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMjA1NjMzNTM5NV5BMl5BanBnXkFtZTgwMDI2MTgwMzE@._V1_SX300.jpg",
                    "imdbId": "tt5890990",
                    "imdbRating": null,
                    "imdbVotes": null
                },
                {
                    "number": 8,
                    "title": "The Great Flood",
                    "year": 2014,
                    "length": 44,
                    "storyline": "It is a story told in more than twelve hundred cultures around the world--the gods sent a great flood to wipe out humanity for its disobedient and wicked ways. Do these numerous creation ...",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMTc0NzQ3MTU4NF5BMl5BanBnXkFtZTgwNDkwNDg3MzE@._V1_SX300.jpg",
                    "imdbId": "tt4261390",
                    "imdbRating": 7.6,
                    "imdbVotes": 55
                },
                {
                    "number": 9,
                    "title": "Aliens and the Civil War",
                    "year": 2015,
                    "length": 44,
                    "storyline": "The Civil War nearly destroyed the great experiment known as the United States of America, but is it possible that the preservation of the republic was influenced by extraterrestrial beings...",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMjI4NTQzNjgwMl5BMl5BanBnXkFtZTgwMzYwNzcyNTE@._V1_SX300.jpg",
                    "imdbId": "tt4562134",
                    "imdbRating": 6.7,
                    "imdbVotes": 54
                }
            ]
        },
        {
            "number": 10,
            "episodes": [
                {
                    "number": 1,
                    "title": "Aliens B.C.",
                    "year": 2015,
                    "length": 44,
                    "storyline": "Enormous manmade caves... Unexplainable structures... And underwater discoveries that challenge everything we know about the past...Is it possible that an advanced civilization inhabited Earth thousands of years ago?",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMTY0NzUwNTE0MF5BMl5BanBnXkFtZTgwMjkzMTczNjE@._V1_SX300.jpg",
                    "imdbId": "tt4865474",
                    "imdbRating": 8.2,
                    "imdbVotes": 32
                },
                {
                    "number": 2,
                    "title": "NASA\'s Secret Agenda",
                    "year": 2015,
                    "length": 44,
                    "storyline": "When German aerospace engineer Wernher von Braun joined the U.S. military and space programs after World War II, he quickly became one of the foremost scientists credited with developing ...",
                    "thumbnail": "https://ia.media-imdb.com/images/M/MV5BMjc5MDQ1MzQ1OV5BMl5BanBnXkFtZTgwMTA4MDM2NjE@._V1_SX300.jpg",
                    "imdbId": "tt4913690",
                    "imdbRating": 8.3,
                    "imdbVotes": 40
                },
                {
                    "number": 3,
                    "title": "Aliens and Robots",
                    "year": 2015,
                    "length": 44,
                    "storyline": "Incredible strength... Superior intelligence... And even the ability to reproduce. In the 21st century, robots are being programmed to do everything from performing surgery on humans--to exploring other planets.",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMTkzOTE5MDQ5Nl5BMl5BanBnXkFtZTgwMjQyMDA1NjE@._V1_SX300.jpg",
                    "imdbId": "tt4887752",
                    "imdbRating": 7.9,
                    "imdbVotes": 32
                },
                {
                    "number": 5,
                    "title": "The Alien Evolution",
                    "year": 2015,
                    "length": 43,
                    "storyline": "Ancient Astronaut theorists have suggested that many of the divine beings depicted in cultures throughout the world are not really divine--or mythological--but instead reflect ...",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMjEzNzYyMDU1OF5BMl5BanBnXkFtZTgwNTU3MTA3NjE@._V1_SX300.jpg",
                    "imdbId": "tt4929188",
                    "imdbRating": 7.8,
                    "imdbVotes": 37
                },
                {
                    "number": 7,
                    "title": "Creatures of the Deep",
                    "year": 2015,
                    "length": 43,
                    "storyline": "In August of 2014, Russian cosmonauts aboard the International Space Station discovered something incredibly unexpected covering parts of the windows--living sea plankton. If sea life can thrive in space, might the reverse be true as well? Might there be alien life forms that inhabit our seas? Throughout history, there have been tales of strange creatures hiding in Earth\'s waters.",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMTcyMzI5MDkzM15BMl5BanBnXkFtZTgwODU1NzM3NjE@._V1_SX300.jpg",
                    "imdbId": "tt4964354",
                    "imdbRating": 7.1,
                    "imdbVotes": 31
                },
                {
                    "number": 8,
                    "title": "Circles from the Sky",
                    "year": 2015,
                    "length": 43,
                    "storyline": "Over 10,000 crop circles have been reported worldwide, appearing in 50 different countries, and nearly every continent on Earth. These strange designs pressed into crop fields range from simple circles to elaborate pictograms thousand of feet long. The phenomenon has been associated with scientific anomalies, strange lights, and even UFO activity.",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMTU1ODgwNjcxNV5BMl5BanBnXkFtZTgwMDAzMzY4NjE@._V1_SX300.jpg",
                    "imdbId": "tt5004680",
                    "imdbRating": 8.6,
                    "imdbVotes": 33
                },
                {
                    "number": 10,
                    "title": "The Forbidden Zones",
                    "year": 2015,
                    "length": 42,
                    "storyline": "Specific regions of the world that are off-limits to exploration due to warfare, instability and inaccessibility.",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMTZkNjYwZjYtZWJiOC00Mjk5LTg4MGUtZWM1NmU4MTE0MjQ0XkEyXkFqcGdeQXVyMzQ5OTk4OQ@@._V1_SX300.jpg",
                    "imdbId": "tt7349878",
                    "imdbRating": 8.5,
                    "imdbVotes": 11
                }
            ]
        },
        {
            "number": 11,
            "episodes": [
                {
                    "number": 1,
                    "title": "Pyramids of Antarctica",
                    "year": 2016,
                    "length": 42,
                    "storyline": "Recent discovery of Pyramid shaped objects in Antarctica have Ancient Astronaut theorists abuzz. With stories of Nazi expeditions and government reports of strange activity and sightings, Antarctica seems to be hiding something beneath its icy surface. Could it be extraterrestrials?",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMjA1NjMzNTM5NV5BMl5BanBnXkFtZTgwMDI2MTgwMzE@._V1_SX300.jpg",
                    "imdbId": "tt5660732",
                    "imdbRating": 8.6,
                    "imdbVotes": 43
                },
                {
                    "number": 2,
                    "title": "Destination Mars",
                    "year": 2016,
                    "length": 42,
                    "storyline": "Within 20 years, we are expecting to send the first colonizers to the Red Planet. Astronauts will brave the six-month journey to become the first extraterrestrials visitors to Mars--where they are expected to live and die there, never to return to Earth. But why have we always had a strange fascination with this particular planet? The latest NASA research has revealed that not only is there evidence of liquid water on the planet, but that life on Earth was most likely seeded on Mars. Ancient Astronaut theorists even suggest that the development of life on Earth may have similarities to how scientists are currently planning to keep humans alive on the red planet. Could extraterrestrials from Mars have colonized Earth in our remote past? Could our voyage to Mars not only unlock the mysteries of our ancient, extraterrestrial origins--but also be the key to the long-term survival of the human species?",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMjA1NjMzNTM5NV5BMl5BanBnXkFtZTgwMDI2MTgwMzE@._V1_SX300.jpg",
                    "imdbId": "tt5679862",
                    "imdbRating": 9.1,
                    "imdbVotes": 37
                },
                {
                    "number": 3,
                    "title": "The Next Humans",
                    "year": 2016,
                    "length": 42,
                    "storyline": "In the 21st century, technology is heading in a radical new direction--the merging of man and machine. Smart drugs, nanorobots, and machine-printed organs are just a few of the cutting-edge...",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMTZkNjYwZjYtZWJiOC00Mjk5LTg4MGUtZWM1NmU4MTE0MjQ0XkEyXkFqcGdeQXVyMzQ5OTk4OQ@@._V1_SX300.jpg",
                    "imdbId": "tt5704184",
                    "imdbRating": 8,
                    "imdbVotes": 47
                },
                {
                    "number": 4,
                    "title": "The New Evidence",
                    "year": 2016,
                    "length": 42,
                    "storyline": null,
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMjA1NjMzNTM5NV5BMl5BanBnXkFtZTgwMDI2MTgwMzE@._V1_SX300.jpg",
                    "imdbId": "tt5830542",
                    "imdbRating": 8.3,
                    "imdbVotes": 27
                },
                {
                    "number": 5,
                    "title": "The Visionaries",
                    "year": 2016,
                    "length": 42,
                    "storyline": "Scientists theorize that when mankind encounters extraterrestrials, we\'ll need to speak to them through the universal language of mathematics and binary code.",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMjA1NjMzNTM5NV5BMl5BanBnXkFtZTgwMDI2MTgwMzE@._V1_SX300.jpg",
                    "imdbId": "tt5764298",
                    "imdbRating": 8.2,
                    "imdbVotes": 27
                },
                {
                    "number": 6,
                    "title": "Decoding the Cosmic Egg",
                    "year": 2016,
                    "length": 42,
                    "storyline": "A \"Cosmic Egg\" -- in which the universe, or some primordial being comes into existence by the hatching of an egg -- is found in the creation myths of many cultures. Why is this concept so pervasive throughout the world? Clues to the origin of the Cosmic Egg may lie in another symbol commonly paired with the egg -- the symbol of intertwined snakes -- which some believe represents ancient knowledge of the DNA double-helix spiral.",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMjA1NjMzNTM5NV5BMl5BanBnXkFtZTgwMDI2MTgwMzE@._V1_SX300.jpg",
                    "imdbId": "tt5805998",
                    "imdbRating": 8.2,
                    "imdbVotes": 27
                },
                {
                    "number": 7,
                    "title": "The Wisdom Keepers",
                    "year": 2016,
                    "length": 42,
                    "storyline": "The Aborigines of Australia are the oldest, continuously surviving society on Earth. The stories preserved in their oral tradition are said to date back over 60,000 years",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMjA1NjMzNTM5NV5BMl5BanBnXkFtZTgwMDI2MTgwMzE@._V1_SX300.jpg",
                    "imdbId": "tt5805996",
                    "imdbRating": 8.4,
                    "imdbVotes": 32
                },
                {
                    "number": 8,
                    "title": "The Mysterious Nine",
                    "year": 2016,
                    "length": 42,
                    "storyline": "In 2013, former Canadian Minister of Defense, Paul Hellyer, made a shocking claim--that there is a federation of extraterrestrial beings monitoring and guiding humanity. But why would such an esteemed politician make such a controversial announcement? Ancient Astronaut theorists believe evidence can be found throughout history to prove his claims are true. Stories of emperors, kings and pharaohs consulting a pantheon of nine gods can be found in virtually every culture across the globe.",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMjA1NjMzNTM5NV5BMl5BanBnXkFtZTgwMDI2MTgwMzE@._V1_SX300.jpg",
                    "imdbId": "tt5837770",
                    "imdbRating": 8.4,
                    "imdbVotes": 36
                },
                {
                    "number": 9,
                    "title": "The Hidden Empire",
                    "year": 2016,
                    "length": 90,
                    "storyline": null,
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMjA1NjMzNTM5NV5BMl5BanBnXkFtZTgwMDI2MTgwMzE@._V1_SX300.jpg",
                    "imdbId": "tt5888906",
                    "imdbRating": 8.3,
                    "imdbVotes": 6
                },
                {
                    "number": 10,
                    "title": "The Prototypes",
                    "year": 2016,
                    "length": 42,
                    "storyline": "Recent archaeological discoveries reveal a very different picture of what life on Earth looked like in the remote past. It was a real-life version of \"Lord of the Rings,\" where humans shared the planet with various different types of intelligent human-like species, such as Neanderthals, Denisovans, and even hobbits. But how is it that these various hominid species developed separately in isolated pockets of the globe? And why does no fossil evidence exist suggesting a slow gradual delineation from a common species? Ancient Astronaut theorists suggest these different intelligent species seem to have come straight out of the incubator, and that Earth itself may have served as an extraterrestrial laboratory. A laboratory where different human prototypes once existed--but only one species was chosen to survive. Is mankind the result of an extraterrestrial experiment?",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMjA1NjMzNTM5NV5BMl5BanBnXkFtZTgwMDI2MTgwMzE@._V1_SX300.jpg",
                    "imdbId": "tt5872080",
                    "imdbRating": 8.4,
                    "imdbVotes": 32
                },
                {
                    "number": 11,
                    "title": "Space Station Moon",
                    "year": 2016,
                    "length": 42,
                    "storyline": "Mythology has lore about the sky before there was a moon. Many strange stories intertwined with first hand experience from Apollo Astronauts who went to the moon. Lights , blurry glowing objects have been seen on the moon since 1835. As the moon may even be hollow and carted into position.",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMjA1NjMzNTM5NV5BMl5BanBnXkFtZTgwMDI2MTgwMzE@._V1_SX300.jpg",
                    "imdbId": "tt5889850",
                    "imdbRating": 8.6,
                    "imdbVotes": 18
                },
                {
                    "number": 11,
                    "title": "Space Station Moon",
                    "year": 2016,
                    "length": 42,
                    "storyline": "Mythology has lore about the sky before there was a moon. Many strange stories intertwined with first hand experience from Apollo Astronauts who went to the moon. Lights , blurry glowing objects have been seen on the moon since 1835. As the moon may even be hollow and carted into position.",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BN2Q3OTBiYTUtMGE5Ni00N2I3LWE3MjctNzk4YWM4NWQzZjE5XkEyXkFqcGdeQXVyMTEzNzczMA@@._V1_SX300.jpg",
                    "imdbId": "tt5889356",
                    "imdbRating": 8.3,
                    "imdbVotes": 27
                },
                {
                    "number": 12,
                    "title": "Russia\'s Secret Files",
                    "year": 2016,
                    "length": 42,
                    "storyline": "Russia is a vast country, spanning over 6 million square miles of some of the most rugged terrain on Earth, much of which is uninhabited and largely unexplored. It is also the home of \'cosmism\' -- a belief that human civilization originated in the cosmos.",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMjA1NjMzNTM5NV5BMl5BanBnXkFtZTgwMDI2MTgwMzE@._V1_SX300.jpg",
                    "imdbId": "tt5962332",
                    "imdbRating": 9.1,
                    "imdbVotes": 8
                },
                {
                    "number": 13,
                    "title": "Beyond Roswell",
                    "year": 2016,
                    "length": 42,
                    "storyline": "The U.S. military has long denied that the infamous 1947 Roswell crash was anything other than a weather balloon, but FBI files released in 2011 include a memo to J. Edgar Hoover regarding the recovery of not one, but three \"flying saucers.\" Could the U.S. government really have extraterrestrial crafts in their possession? Ancient Astronaut theorists say yes, and claim that Roswell is just the tip of the iceberg.",
                    "thumbnail": null,
                    "imdbId": "tt5986072",
                    "imdbRating": 8.8,
                    "imdbVotes": 8
                },
                {
                    "number": 14,
                    "title": "The Returned",
                    "year": 2016,
                    "length": 42,
                    "storyline": "Thousands of people from every part of the world have reported being abducted by aliens, including even a world leader - President Kirsan Ilyumzhinov of the Russian Republic of Kalmykia, who announced in 2007 that he had been taken aboard an extraterrestrial ship. Could the alien abduction phenomenon be true? But if so, why are people being taken? And perhaps more importantly - why are they being returned?",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BNGEwYjQzNWEtZWQ2ZC00NzJiLThhMjUtNDgyYzA1NjIzODE4XkEyXkFqcGdeQXVyMTEzNzczMA@@._V1_SX300.jpg",
                    "imdbId": "tt5964040",
                    "imdbRating": 9,
                    "imdbVotes": 32
                },
                {
                    "number": 15,
                    "title": "Shiva the Destroyer",
                    "year": 2016,
                    "length": 42,
                    "storyline": "Of the millions of Hindu gods, none is more important than Shiva--the great destroyer. Ancient Astronaut theorists suggest this powerful being that possessed flying machines, incredible weapons, and had supernatural abilities may, in fact, have been the leader of an extraterrestrial faction that came to Earth wielding advanced technology",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMjA1NjMzNTM5NV5BMl5BanBnXkFtZTgwMDI2MTgwMzE@._V1_SX300.jpg",
                    "imdbId": "tt5985986",
                    "imdbRating": 8.7,
                    "imdbVotes": 18
                }
            ]
        },
        {
            "number": 12,
            "episodes": [
                {
                    "number": 2,
                    "title": "Forged by the Gods",
                    "year": 2017,
                    "length": 42,
                    "storyline": "Artificial metallic objects dating back hundreds of thousands of years. New discoveries of metals once thought to be mythological. Mysterious metal spheres defying the laws of physics. Could these out of place artifacts be physical evidence of extraterrestrials visiting planet Earth in the distant past, leaving behind remnants of their technology?",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BY2Y0ZmRjYTYtM2EzMi00NTUyLTkzNTktNDMxOWQ1NGUwNjQyXkEyXkFqcGdeQXVyNzU1NDE4MDc@._V1_SX300.jpg",
                    "imdbId": "tt6846706",
                    "imdbRating": 8.8,
                    "imdbVotes": 18
                },
                {
                    "number": 3,
                    "title": "The Mystery of Rudloe Manor",
                    "year": 2017,
                    "length": 42,
                    "storyline": "In the southwest corner of the Wiltshire, England countryside lies the mysterious structure known as Rudloe Manor. On the surface, it appears to be nothing more than a quaint English manor house, but researchers have argued for years that it has been the epicenter of British research into UFOs since World War II.",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMjA1NjMzNTM5NV5BMl5BanBnXkFtZTgwMDI2MTgwMzE@._V1_SX300.jpg",
                    "imdbId": "tt6846714",
                    "imdbRating": 8.7,
                    "imdbVotes": 20
                },
                {
                    "number": 4,
                    "title": "The Alien Architects",
                    "year": 2017,
                    "length": 42,
                    "storyline": "Many of the architectural marvels of the ancient world bear striking similarities to one another, even though they were built by civilizations that were separated by thousands of miles and are believed to have had no contact with one another. Separated by more than 7500 miles, the Giza pyramid complex in Giza, Egypt and the Avenue of the Dead in Teotihuacan, Mexico contain pyramids that are aligned with the constellation of Orion\'s belt. An ancient pyramid temple in Cambodia known as Baksei Chamkrong is an almost identical match for the Temple of the Jaguar in Guatemala. Many structures built using stone slabs that weighed over a hundred tons. But how did primitive people achieve such extraordinary feats? And why do we see such similarities? Could these sites have all been designed by alien architects?",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMjA1NjMzNTM5NV5BMl5BanBnXkFtZTgwMDI2MTgwMzE@._V1_SX300.jpg",
                    "imdbId": "tt6913466",
                    "imdbRating": 8.7,
                    "imdbVotes": 7
                },
                {
                    "number": 5,
                    "title": "The Pharaohs\' Curse",
                    "year": 2017,
                    "length": 42,
                    "storyline": "Does the curse of King Tut\'s tomb truly exist? Could it be an alien technology used to protect past secrets? Or be a great power, like the Ark of the Covenant? And like the Ark could it still exist, possibly somewhere buried on Oak Island?",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMWM2NTIwYjctYmVjNS00Zjk3LTk2MTMtZjU5MDUwNTg0MTU0XkEyXkFqcGdeQXVyNDg4NjA0MzY@._V1_SX300.jpg",
                    "imdbId": "tt6945750",
                    "imdbRating": 7.6,
                    "imdbVotes": 52
                },
                {
                    "number": 6,
                    "title": "The Science Wars",
                    "year": 2017,
                    "length": 42,
                    "storyline": "Anthropologists, Archaeologists, Geologists and others are at odds as their scientific paradigms conflict. New discoveries and evidence challenging historical facts are ignored. Could these clues explain more of our past or our origins?",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMGRmM2I2MjAtMTdjZi00NmQ0LTkwMTYtZjRmN2YyYmM0NDEwXkEyXkFqcGdeQXVyMTEzNzczMA@@._V1_SX300.jpg",
                    "imdbId": "tt6970362",
                    "imdbRating": 8,
                    "imdbVotes": 50
                },
                {
                    "number": 7,
                    "title": "City of the Gods",
                    "year": 2017,
                    "length": 42,
                    "storyline": "The ancient site of Teotihuacan in central Mexico--which the Aztecs called the \"City of the Gods\"--is one of the world\'s greatest mysteries. To this day, no one knows who built the ...",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BZTA1YTc1NTAtMDliMC00ZWEzLTg1OTYtNDI5OWQ0ODdkOWVhXkEyXkFqcGdeQXVyMTEzNzczMA@@._V1_SX300.jpg",
                    "imdbId": "tt6998828",
                    "imdbRating": 7.7,
                    "imdbVotes": 50
                },
                {
                    "number": 8,
                    "title": "The Alien Frequency",
                    "year": 2017,
                    "length": 42,
                    "storyline": "The use of mantras and chanting to connect with a higher plane has existed in cultures all over the world.",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BYWRmOGM0MWMtNGVkNy00MzI2LWI0NTItM2MwNzZjYjYwZmRkXkEyXkFqcGdeQXVyNDg4NjA0MzY@._V1_SX300.jpg",
                    "imdbId": "tt7027186",
                    "imdbRating": 8,
                    "imdbVotes": 46
                },
                {
                    "number": 9,
                    "title": "The Majestic Twelve",
                    "year": 2017,
                    "length": 90,
                    "storyline": null,
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BNTc1MDFmOTktNDhiMi00MjliLWE4OTctODlkNjhmYjcyY2RjXkEyXkFqcGdeQXVyNDg4NjA0MzY@._V1_SX300.jpg",
                    "imdbId": "tt7130410",
                    "imdbRating": 9.1,
                    "imdbVotes": 23
                },
                {
                    "number": 10,
                    "title": "The Akashic Record",
                    "year": 2017,
                    "length": 42,
                    "storyline": "The Akashic Record or Book of Life is the keeper of all knowledge. Though part of mythology, new scientific discoveries suggest it could actually exist. Could this lead to our connecting telepathically with other beings in the universe?",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMDU2MDQwMGUtM2FhMy00Y2JiLThmOWItZDcwMGNjNTE3MjYzXkEyXkFqcGdeQXVyMTEzNzczMA@@._V1_SX300.jpg",
                    "imdbId": "tt7185450",
                    "imdbRating": 7.5,
                    "imdbVotes": 50
                },
                {
                    "number": 11,
                    "title": "Voices of the Gods",
                    "year": 2017,
                    "length": 42,
                    "storyline": "Text written in Sanskrit, the language of ancient India, describe modern day technologies with surprising accuracy. This is attributed to Hindu gods being on earth while writing these texts. Could these gods actually be ancient aliens?",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMTRjMDAzYmUtYjNkMS00Mjg2LTk5YmEtYzNhMTcxZThkNDIxXkEyXkFqcGdeQXVyNDg4NjA0MzY@._V1_SX300.jpg",
                    "imdbId": "tt7248348",
                    "imdbRating": 7.6,
                    "imdbVotes": 35
                },
                {
                    "number": 12,
                    "title": "The Animal Agenda",
                    "year": 2017,
                    "length": 42,
                    "storyline": "Extraterrestrials are usually depicted with bodies like humans, but what if they actually look more like animals? Many past civilizations revered certain animals as if they were gods. Could these animals originate from other worlds?",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BODg2OTgzNWEtYmUzYS00YjU3LThmYzktN2M2NDA5NTE3MGE5XkEyXkFqcGdeQXVyNDg4NjA0MzY@._V1_SX300.jpg",
                    "imdbId": "tt7250190",
                    "imdbRating": 7.2,
                    "imdbVotes": 38
                },
                {
                    "number": 13,
                    "title": "The Replicants",
                    "year": 2017,
                    "length": 42,
                    "storyline": "Across the globe, over 1.5 billion people believe in reincarnation, the belief that a soul can be reborn into a different body after death.",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BYjAxN2EwN2YtMjAzYy00N2UxLTk5MjEtZjg0MmE4MzFjMjk2XkEyXkFqcGdeQXVyMTEzNzczMA@@._V1_SX300.jpg",
                    "imdbId": "tt7250192",
                    "imdbRating": 7.4,
                    "imdbVotes": 39
                },
                {
                    "number": 14,
                    "title": "A Spaceship Made of Stone",
                    "year": 2017,
                    "length": 90,
                    "storyline": "Japan\'s legends about the gods or Kami as they call them are being told all over Japan. Many places all over the country feature extraordinary large memorials for these \"Gods\" which have uncommon architecture and stories.",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BNWU0OGJlZjQtODAxZS00ZTViLWI4ZWYtYjYzZmMzMDlmYmI5XkEyXkFqcGdeQXVyNDg4NjA0MzY@._V1_SX300.jpg",
                    "imdbId": "tt7250194",
                    "imdbRating": 7.8,
                    "imdbVotes": 14
                },
                {
                    "number": 15,
                    "title": "The Alien Disks",
                    "year": 2017,
                    "length": 90,
                    "storyline": null,
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMjA1NjMzNTM5NV5BMl5BanBnXkFtZTgwMDI2MTgwMzE@._V1_SX300.jpg",
                    "imdbId": "tt7349266",
                    "imdbRating": 7.5,
                    "imdbVotes": 13
                },
                {
                    "number": 16,
                    "title": "Return to Gobekli Tepe",
                    "year": 2017,
                    "length": 42,
                    "storyline": "The ancient site of Gobekli Tepe challenges everything modern archaeologists believe about mankind\'s past.",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BNWE3ZjdkMzEtNjBjZC00MjNjLTllNDEtMjJjYWYyM2QyYjczXkEyXkFqcGdeQXVyODU4Mzk1Njk@._V1_SX300.jpg",
                    "imdbId": "tt7410250",
                    "imdbRating": 7.9,
                    "imdbVotes": 41
                }
            ]
        },
        {
            "number": 13,
            "episodes": [
                {
                    "number": 1,
                    "title": "The UFO Conspiracy",
                    "year": 2018,
                    "length": 90,
                    "storyline": "In 2017, it was revealed that the Department of Defense recently spent millions investigating reports of UFOs. This is the latest disclosure about a series of secret government UFO projects.",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BYzcwMDU2MjItY2RmYy00YjY3LTllNzctMGE5ZjcyOTUyYTliXkEyXkFqcGdeQXVyMTkyNzcwMjg@._V1_SX300.jpg",
                    "imdbId": "tt8346728",
                    "imdbRating": 8.5,
                    "imdbVotes": 30
                },
                {
                    "number": 2,
                    "title": "Da Vinci\'s Forbidden Codes",
                    "year": 2018,
                    "length": 42,
                    "storyline": "Researchers claim there are hidden messages that can be found within the works of Leonardo da Vinci--and other art throughout history--that may reveal an extraterrestrial influence.",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BNzQzYmM0OTktZTY1ZS00OGQ3LWFlODQtN2RkYjc0MTMxNDllXkEyXkFqcGdeQXVyODkzMTQ1Njk@._V1_SX300.jpg",
                    "imdbId": "tt8346896",
                    "imdbRating": 8.2,
                    "imdbVotes": 24
                },
                {
                    "number": 3,
                    "title": "The Alien Protocols",
                    "year": 2018,
                    "length": 42,
                    "storyline": "In 2017, a strange, cigar-shaped object entered our solar system from another star system traveling in such an unusual trajectory it caused some astronomers to suggest that it could be an ...",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BNmQ5NjljYTYtMzdmNi00YmRlLWI5NGMtZmMzM2VjZDQ5YzhlXkEyXkFqcGdeQXVyODg0Mzk5ODY@._V1_SX300.jpg",
                    "imdbId": "tt8346898",
                    "imdbRating": 8.2,
                    "imdbVotes": 22
                },
                {
                    "number": 4,
                    "title": "Earth\'s Black Holes",
                    "year": 2018,
                    "length": 42,
                    "storyline": "Could black holes exist not just in outer space, but here on Earth? And if so, could Earth\'s Black Holes have caused strange disappearances and other inexplicable phenomena for centuries?",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BM2JlNzRiODktOTU4ZC00Y2U1LWI1NWQtOWYwNDNhZTNmMTAzXkEyXkFqcGdeQXVyNjY1MTQ4OTM@._V1_SX300.jpg",
                    "imdbId": "tt8439158",
                    "imdbRating": 8.1,
                    "imdbVotes": 13
                },
                {
                    "number": 5,
                    "title": "The Desert Codes",
                    "year": 2018,
                    "length": 42,
                    "storyline": "Could giant ancient drawings found etched into the desert floor be part of an ancient alien code?",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMTE1YjY5NzUtOGZmMi00YzdhLWJlZmUtMDU3ZDFmNzIwMzhiXkEyXkFqcGdeQXVyODkzMTQ1Njk@._V1_SX300.jpg",
                    "imdbId": "tt8479900",
                    "imdbRating": 8.8,
                    "imdbVotes": 14
                },
                {
                    "number": 6,
                    "title": "Area 52",
                    "year": 2018,
                    "length": 90,
                    "storyline": "Could Area 51\'s notoriety act as a cover up to much bigger, top-secret extraterrestrial experiments taking place at multiple locations?",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMDFlZGQwMmYtYjI1ZC00MmMyLWJhZWUtNTNhNWQ2NWFlYWQyXkEyXkFqcGdeQXVyODkzMTQ1Njk@._V1_SX300.jpg",
                    "imdbId": "tt8567162",
                    "imdbRating": 8.6,
                    "imdbVotes": 13
                },
                {
                    "number": 7,
                    "title": "Earth Station Egypt",
                    "year": 2018,
                    "length": 84,
                    "storyline": "Was Egypt home to Earth\'s earliest extraterrestrial visitors? Ancient Astronaut theorist Giorgio A. Tsoukalos explores the latest scientific discoveries at some of the world\'s most ancient ...",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMTZkNjYwZjYtZWJiOC00Mjk5LTg4MGUtZWM1NmU4MTE0MjQ0XkEyXkFqcGdeQXVyMzQ5OTk4OQ@@._V1_SX300.jpg",
                    "imdbId": "tt8750648",
                    "imdbRating": null,
                    "imdbVotes": null
                },
                {
                    "number": 8,
                    "title": "Island of the Giants",
                    "year": 2018,
                    "length": 41,
                    "storyline": "Could the ancient ruins of Sardinia, Italy hold clues to an extraterrestrial past? Oak Island treasure hunter Marty Lagina and Ancient Astronaut theorist Giorgio A. Tsoukalos explore the ...",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMTZkNjYwZjYtZWJiOC00Mjk5LTg4MGUtZWM1NmU4MTE0MjQ0XkEyXkFqcGdeQXVyMzQ5OTk4OQ@@._V1_SX300.jpg",
                    "imdbId": "tt8764612",
                    "imdbRating": 7.6,
                    "imdbVotes": 26
                },
                {
                    "number": 9,
                    "title": "The Taken",
                    "year": 2018,
                    "length": 42,
                    "storyline": "Abduction stories around the world are analyzed.",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMTZkNjYwZjYtZWJiOC00Mjk5LTg4MGUtZWM1NmU4MTE0MjQ0XkEyXkFqcGdeQXVyMzQ5OTk4OQ@@._V1_SX300.jpg",
                    "imdbId": "tt8764614",
                    "imdbRating": 7.5,
                    "imdbVotes": 28
                },
                {
                    "number": 10,
                    "title": "The Sentinels",
                    "year": 2018,
                    "length": 42,
                    "storyline": "Seven stone giants stand guard over a Pacific island. But what are they protecting--and from whom? Ancient Astronaut theorists Giorgio A. Tsoukalos and David H. Childress explore a Polynesian land of bizarre figures",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMTM5NjlkM2EtYTEyMi00N2M4LTg3ZDAtYTZmYTE3ZGViNDhmXkEyXkFqcGdeQXVyODkzMTQ1Njk@._V1_SX300.jpg",
                    "imdbId": "tt8825242",
                    "imdbRating": 7.7,
                    "imdbVotes": 27
                },
                {
                    "number": 11,
                    "title": "Russia Declassified",
                    "year": 2018,
                    "length": 42,
                    "storyline": "A look at suspicions that the U.S. and Russia have been working together to prepare for an extraterrestrial encounter.",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMTZkNjYwZjYtZWJiOC00Mjk5LTg4MGUtZWM1NmU4MTE0MjQ0XkEyXkFqcGdeQXVyMzQ5OTk4OQ@@._V1_SX300.jpg",
                    "imdbId": "tt8866172",
                    "imdbRating": 7.7,
                    "imdbVotes": 18
                },
                {
                    "number": 12,
                    "title": "They Came from the Sky",
                    "year": 2018,
                    "length": 42,
                    "storyline": "Ancient cultures from around the world believed that meteorites were not merely rocks that fell from the sky, but sacred stones imbued with the power of the Gods.",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMTZkNjYwZjYtZWJiOC00Mjk5LTg4MGUtZWM1NmU4MTE0MjQ0XkEyXkFqcGdeQXVyMzQ5OTk4OQ@@._V1_SX300.jpg",
                    "imdbId": "tt8905788",
                    "imdbRating": 7.2,
                    "imdbVotes": 25
                },
                {
                    "number": 13,
                    "title": "The Artificial Human",
                    "year": 2018,
                    "length": 84,
                    "storyline": "Are intelligent robots a threat to humanity - or the next step in human evolution? As man and machine begin to merge, are we fulfilling a destiny prepared for us by aliens thousands of years agö",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMTZkNjYwZjYtZWJiOC00Mjk5LTg4MGUtZWM1NmU4MTE0MjQ0XkEyXkFqcGdeQXVyMzQ5OTk4OQ@@._V1_SX300.jpg",
                    "imdbId": "tt8937624",
                    "imdbRating": 6.4,
                    "imdbVotes": 18
                },
                {
                    "number": 14,
                    "title": "The Alien Phenomenon",
                    "year": 2019,
                    "length": 84,
                    "storyline": "Erich von D niken\'s Chariots of the Gods? challenged everything we thought we knew about mankind\'s history, suggesting that aliens visited Earth in the distant past. Fifty years after the book\'s publication, is he about to be proven right?",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMTZkNjYwZjYtZWJiOC00Mjk5LTg4MGUtZWM1NmU4MTE0MjQ0XkEyXkFqcGdeQXVyMzQ5OTk4OQ@@._V1_SX300.jpg",
                    "imdbId": "tt9559854",
                    "imdbRating": 7.2,
                    "imdbVotes": 5
                },
                {
                    "number": 15,
                    "title": "Return to Mars",
                    "year": 2019,
                    "length": 84,
                    "storyline": "As humanity prepares to establish colonies beyond Earth, are we simply realizing mankind\'s future or returning to its extraterrestrial past?",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMTZkNjYwZjYtZWJiOC00Mjk5LTg4MGUtZWM1NmU4MTE0MjQ0XkEyXkFqcGdeQXVyMzQ5OTk4OQ@@._V1_SX300.jpg",
                    "imdbId": "tt9690906",
                    "imdbRating": 5.9,
                    "imdbVotes": 16
                }
            ]
        },
        {
            "number": 14,
            "episodes": [
                {
                    "number": 1,
                    "title": "Return to Antarctica",
                    "year": 2019,
                    "length": 42,
                    "storyline": "Has Antarctica served as a home base for extraterrestrial visitors to Earth for thousands of years? As human exploration of the frozen continent increases, are we about to come face to face...",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BNDE3NTRiNjYtYjQ3NS00Mjk1LTk2ODMtNjE5MDAzYzljMDUyXkEyXkFqcGdeQXVyMTA1MTUxMjEz._V1_SX300.jpg",
                    "imdbId": "tt10454582",
                    "imdbRating": 8,
                    "imdbVotes": 45
                },
                {
                    "number": 2,
                    "title": "The Badlands Guardian",
                    "year": 2019,
                    "length": 42,
                    "storyline": "Satellite images have revealed a giant face that appears to be carved into the Earth in Alberta, Canada, which has now become known far and wide as \"The Badlands Guardian.\" Could it be part...",
                    "thumbnail": null,
                    "imdbId": "tt10454650",
                    "imdbRating": 6.9,
                    "imdbVotes": 36
                },
                {
                    "number": 4,
                    "title": "The Star Gods of Sirius",
                    "year": 2019,
                    "length": 42,
                    "storyline": "Did aliens from the tiny star Sirius B visit humans in the distant past, imparting the knowledge of civilization to peoples thousands of miles apart? And did they leave behind a vital clue ...",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BOGU5ZWRhYzAtMTA0NC00MjZmLTk1YzItOGFmZWZiYjFjZjQ1XkEyXkFqcGdeQXVyMTA1MTUxMjEz._V1_SX300.jpg",
                    "imdbId": "tt10525560",
                    "imdbRating": 7.6,
                    "imdbVotes": 28
                },
                {
                    "number": 5,
                    "title": "They came from the Sea",
                    "year": 2019,
                    "length": 42,
                    "storyline": "Lurking in our oceans are creatures that defy explanation. Octopuses that can edit their own DNA. Animals that can regenerate their entire brain. Jellyfish that can literally live forever. ...",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BZjJlNzFlNDItYTNmOS00ZTU3LWEyYTktZDYxZDMwNWRkN2I2XkEyXkFqcGdeQXVyMTA1MTUxMjEz._V1_SX300.jpg",
                    "imdbId": "tt10594490",
                    "imdbRating": 8.1,
                    "imdbVotes": 37
                },
                {
                    "number": 7,
                    "title": "The Druid Connection",
                    "year": 2019,
                    "length": 42,
                    "storyline": "Did ancient Druids travel to New England and New York\'s Hudson Valley centuries ago, bringing with them the beliefs and technologies of their alien mentors, the Shining Ones of Irish myth? ...",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BYjA2NjYwM2ItODQ5OC00OGQwLWFiYjAtM2RmOWVkYjBjMzMzXkEyXkFqcGdeQXVyMTA1MTUxMjEz._V1_SX300.jpg",
                    "imdbId": "tt10666424",
                    "imdbRating": 7.2,
                    "imdbVotes": 28
                },
                {
                    "number": 8,
                    "title": "The Reptilian Agenda",
                    "year": 2019,
                    "length": 42,
                    "storyline": "Serpent gods are found in the religious traditions of Mesopotamia, Central and South America, India, China and Japan. Even the Bible describes reptilian entities interacting with humans. ...",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BYjNhM2MxOGQtYjZkYy00ZGRiLWFhNzMtY2I4ZDQ3YjY0MTEwXkEyXkFqcGdeQXVyMTEwMDM5MzQ1._V1_SX300.jpg",
                    "imdbId": "tt10746794",
                    "imdbRating": 7.5,
                    "imdbVotes": 25
                },
                {
                    "number": 9,
                    "title": "The Alien Infection",
                    "year": 2019,
                    "length": 42,
                    "storyline": "In 2016, scientists made the astonishing discovery that 30 percent of all protein adaptations since humans\' divergence with chimpanzees have been driven by viruses. Could ancient aliens ...",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMWQyMDk4MzUtZjdhZC00MzQ3LTkxZjktZDQyNmMwMTE3ZjM1XkEyXkFqcGdeQXVyMTEwMDM5MzQ1._V1_SX300.jpg",
                    "imdbId": "tt10746800",
                    "imdbRating": 7.6,
                    "imdbVotes": 25
                },
                {
                    "number": 10,
                    "title": "Project Hybred",
                    "year": 2019,
                    "length": 42,
                    "storyline": "Are extraterrestrials altering human DNA and repopulating the Earth with alien/human hybrids? By following the trail of evidence from ancient cultures to modern-day animal mutilations and ...",
                    "thumbnail": null,
                    "imdbId": "tt10774556",
                    "imdbRating": 7.4,
                    "imdbVotes": 17
                },
                {
                    "number": 11,
                    "title": "The Trans-Dimensionals",
                    "year": 2019,
                    "length": 42,
                    "storyline": "Could paranormal activity be explained by beings entering our world from other dimensions? According to some Ancient Astronaut theorists, entities that have been described as ghosts, angels...",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BMjZmM2ZkZDctYTUzOC00ZDNlLTliYWYtODc5ZDgwNmIyZjZiXkEyXkFqcGdeQXVyMTEwMDM5MzQ1._V1_SX300.jpg",
                    "imdbId": "tt10779146",
                    "imdbRating": 7.7,
                    "imdbVotes": 25
                },
                {
                    "number": 12,
                    "title": "Islands of Fire",
                    "year": 2019,
                    "length": 42,
                    "storyline": "The native Hawaiians trace their ancestry to beings from the Pleiades star system called the Akua. Could these islands have once been inhabited by extraterrestrials? And is it possible that...",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BNmQ4Y2ZjMDMtOWFjNC00YzA2LWJmMTQtYTRhN2Y0YzFjNDQyXkEyXkFqcGdeQXVyMTEwMDM5MzQ1._V1_SX300.jpg",
                    "imdbId": "tt10857174",
                    "imdbRating": 8.4,
                    "imdbVotes": 31
                },
                {
                    "number": 13,
                    "title": "The Constellation Code",
                    "year": 2019,
                    "length": 42,
                    "storyline": "Throughout the ancient world, structures were built to mirror the constellations in the night sky and this is only apparent when they are viewed from high above; a look at the possibility that these structures were designed as messages.",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BYmFlZTNhNTAtZTI1Zi00ZGUyLTlmYTMtZmIzOGE5NzkyOWE5XkEyXkFqcGdeQXVyMTEwMDM5MzQ1._V1_SX300.jpg",
                    "imdbId": "tt10900360",
                    "imdbRating": 6.8,
                    "imdbVotes": 23
                },
                {
                    "number": 14,
                    "title": "The Nuclear Agenda",
                    "year": 2019,
                    "length": 42,
                    "storyline": "Ever since the first atomic bomb was detonated in 1945, UFOs have been reported at nuclear facilities all over the world. Is it possible that extraterrestrials are monitoring humanity\'s ...",
                    "thumbnail": null,
                    "imdbId": "tt10926644",
                    "imdbRating": 7.5,
                    "imdbVotes": 10
                },
                {
                    "number": 17,
                    "title": "The Secrets of Stonehenge",
                    "year": 2019,
                    "length": 90,
                    "storyline": "A shocking DNA study released in 2018 reveals that the original builders of Stonehenge mysteriously vanished.",
                    "thumbnail": "https://m.media-amazon.com/images/M/MV5BYTM4ZWQ3ODktYWVlZi00MjFhLTg4ZmYtNzFmNGM0NTA0MTAxXkEyXkFqcGdeQXVyNDg5MzcxNzA@._V1_SX300.jpg",
                    "imdbId": "tt11142984",
                    "imdbRating": 7.2,
                    "imdbVotes": 18
                }
            ]
        }
    ]
}';**/
        return new JsonResponse($result, 200);
    }

    /**
     * @param array $result
     * @param OMDb $omdb
     * @return Series
     * @throws \Exception
     */
    private function createSeriesDTO(array $result, OMDb $omdb)
    {
        $imdbId = $result['imdbID'];

        $seriesDTO = new Series();
        $seriesDTO->setTitle($result['Title']);
        $seriesDTO->setPlot($result['Plot']);
        $seriesDTO->setPoster($result['Poster']);
        $seriesDTO->setImdbId($result['imdbID']);
        $seriesDTO->setImdbRating($result['imdbRating']);
        $seriesDTO->setImdbVotes($result['imdbVotes']);

        $year = $result['Year'];
        $years = explode('–', $year);

        $yearFrom = intval($years[0]);
        $seriesDTO->setYearFrom($yearFrom);

        if (isset($years[1])) {
            if ($years[1] != null) {
                $yearTo = intval($years[1]);
                $seriesDTO->setYearTo($yearTo);
            }
        }

        $totalSeasons = $result['totalSeasons'];

        for ($i = 1; $i <= $totalSeasons; $i++) {
            $season = $omdb->get_by_id($imdbId, $i);
            $seasonDTO = new Season();
            $seasonDTO->setNumber($i);

            $seriesDTO->addSeason($seasonDTO);

            $episodeSummaries = $season['Episodes'];
            for ($j = 0; $j < count($episodeSummaries); $j++) {
                $episodeSummary = $episodeSummaries[$j];
                $episodeIMDbID = $episodeSummary['imdbID'];

                $episode = $omdb->get_by_id($episodeIMDbID);
                if ($episode['Response'] === false) {
                    continue;
                }

                $episodeDTO = new Episode();
                $episodeDTO->setNumber($episode['Episode']);
                $episodeDTO->setTitle($episode['Title']);
                $episodeDTO->setYear($episode['Year']);
                $episodeDTO->setDuration($episode['Runtime']);
                $episodeDTO->setPlot($episode['Plot']);
                $episodeDTO->setThumbnail($episode['Poster']);
                $episodeDTO->setIMDbID($episode['imdbID']);
                $episodeDTO->setImdbRating($episode['imdbRating']);
                $episodeDTO->setImdbVotes($episode['imdbVotes']);

                $seasonDTO->addEpisode($episodeDTO);
            }
        }

        return $seriesDTO;
    }

    /**
     * @param array $result
     * @return array
     */
    public function serializeMovie(array $result)
    {
        $serialized = [
            'title' => $result['Title'],
            'year' => $result['Year'],
            'imdbId' => $result['imdbID'],
            'type' => $result['Type'],
            'poster' => $result['Poster']
        ];

        if (isset($result['imdbVotes'])) {
            $serialized['imdbVotes'] = $result['imdbVotes'];
        }

        if (isset($result['imdbRating'])) {
            $serialized['imdbRating'] = $result['imdbRating'];
        }

        if (isset($result['Plot'])) {
            $serialized['storyline'] = $result['Plot'];
        }

        if (isset($result['Runtime'])) {
            $serialized['duration'] = $result['Runtime'];
        }

        return $serialized;
    }

    /**
     * @param Series $series
     * @return array
     */
    public function serializeSeries(Series $series)
    {
        $seriesArray = [
            'imdbId' => $series->getImdbId(),
            'title' => $series->getTitle(),
            'storyline' => $series->getPlot(),
            'poster' => $series->getPoster(),
            'year' => $series->getYearFrom() . "-" . $series->getYearTo(),
            'yearFrom' => $series->getYearFrom(),
            'yearTo' => $series->getYearTo(),
            'imdbRating' => $series->getImdbRating(),
            'imdbVotes' => $series->getImdbVotes()
        ];

        $seasonsArray = [];
        foreach ($series->getSeasons() as $season) {
            $seasonArray = [
                'number' => $season->getNumber()
            ];

            $episodesArray = [];
            foreach ($season->getEpisodes() as $episode) {
                $episodeArray = [
                    'number' => $episode->getNumber(),
                    'title' => $episode->getTitle(),
                    'year' => $episode->getYear(),
                    'length' => $episode->getDuration(),
                    'storyline' => $episode->getPlot(),
                    'thumbnail' => $episode->getThumbnail(),
                    'imdbId' => $episode->getImdbID(),
                    'imdbRating' => $episode->getImdbRating(),
                    'imdbVotes' => $episode->getImdbVotes()
                ];

                $episodesArray[] = $episodeArray;
            }

            $seasonArray['episodes'] = $episodesArray;

            $seasonsArray[] = $seasonArray;
        }

        $seriesArray['seasons'] = $seasonsArray;

        return $seriesArray;
    }

    /**
     * @param array $result
     * @return array
     */
    public function serializeEpisode(array $result)
    {
        $serialized = [
            'title' => $result['Title'],
            'year' => $result['Year'],
            'seasonNumber' => $result['Season'],
            'episodeNumber' => $result['Episode'],
            'length' => $result['Runtime'],
            'storyline' => $result['Plot'],
            'poster' => $result['Poster'],
            'imdbId' => $result['imdbID']
        ];

        return $serialized;
    }

    /**
     * @param array $result
     * @return array
     */
    private function serializeSearchResult(array $result)
    {
        $serialized = [
            'title' => $result['Title'],
            'year' => $result['Year'],
            'imdbId' => $result['imdbID'],
            'type' => $result['Type'],
            'poster' => $result['Poster']
        ];

        return $serialized;
    }
}