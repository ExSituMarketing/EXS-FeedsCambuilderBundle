<?php

namespace EXS\FeedsCambuilderBundle\Tests\Service;

use EXS\FeedsCambuilderBundle\Service\FeedsReader;
use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class FeedsReaderTest extends \PHPUnit_Framework_TestCase
{
    private $queryXml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<SMLQuery>
    <Options MaxResults="100" />
    <AvailablePerformers QueryId="123" CountTotalResults="true" PageNum="1" Exact="true">
        <Include />
        <Constraints>
            <StreamType>live</StreamType>
        </Constraints>
    </AvailablePerformers>
</SMLQuery>
XML;

    /**
     * @var string
     */
    private $responseXml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<SMLResult>
    <AvailablePerformers QueryId="123" ExactMatches="10" TotalResultCount="988">
        <Performer Id="15314113" Name="Emma_Frost" Relevance="100" StreamType="live" PartyChat="1" PerfFlag="14174355" Updated="2017-07-02 00:48:16" GoldShow="0" PreGoldShow="1" Waist="24" Age="31" Bust="34" Hips="34" Height="69" Weight="130" Gender="f" SexPref="bi" HairColor="brown" EyeColor="blue" Build="athletic" Theme="toys,housewives" Fetishes="smoking,voyeur,spankingpaddling,roleplay,femdom" Zodiac="aries" Ethnicity="caucasian" BodyMods="tattoos,piercings" CupSize="dd/e" Language="en" PubicHair="bald" Audio="true" FreeChatAudio="true" Phone="false" LanguageISO="en"/>
        <Performer Id="5814046" Name="JennyCouture" Relevance="100" StreamType="live" PartyChat="1" PerfFlag="8931473" Updated="2017-09-12 03:50:39" GoldShow="0" PreGoldShow="0" Waist="26" Age="33" Bust="34" Hips="36" Height="61" Weight="105" Gender="f" SexPref="bi" HairColor="brown" EyeColor="green" Build="athletic" Fetishes="bdsm,feet,anal,dominant,deepthroat" Theme="toys,housewives,bondage" Zodiac="taurus" Ethnicity="caucasian" BodyMods="tattoos,piercings" CupSize="dd/e" Language="en" PubicHair="bald" Audio="true" FreeChatAudio="true" Phone="false" LanguageISO="en"/>
        <Performer Id="1883594" Name="Nikki_Ferrari" Relevance="100" StreamType="live" PartyChat="1" PerfFlag="9980049" Updated="2014-05-31 19:06:58" GoldShow="0" PreGoldShow="1" Waist="26" Age="27" Hips="33" Bust="34" Height="71" Weight="105" Gender="f" SexPref="bi" HairColor="brown" EyeColor="hazel" Build="petite" Theme="toys" Fetishes="anal,underwear,spankingpaddling,deepthroat,gagging" Zodiac="virgo" Ethnicity="hispanic" CupSize="d" Language="en" PubicHair="bald" Audio="true" FreeChatAudio="true" Phone="false" LanguageISO="en"/>
        <Performer Id="4108253" Name="LoneStarAngel" Relevance="100" StreamType="live" PartyChat="1" PerfFlag="8931473" Updated="2014-07-09 07:27:38" GoldShow="0" PreGoldShow="0" Waist="26" Bust="36" Hips="36" Age="40" Height="63" Weight="125" Gender="f" SexPref="bi" HairColor="brown" EyeColor="brown" Build="slender" Fetishes="feet,underwear,voyeur,stockingsnylons" Theme="toys,housewives" Zodiac="taurus" Ethnicity="caucasian" CupSize="a" Language="en" PubicHair="trimmed" Audio="true" FreeChatAudio="true" Phone="false" LanguageISO="en"/>
        <Performer Id="4923315" Name="ErikaXstacy" Relevance="100" StreamType="live" PartyChat="1" PerfFlag="8931473" Updated="2017-07-14 23:54:13" GoldShow="0" PreGoldShow="0" Waist="32" Bust="36" Age="36" Hips="50" Height="60" Weight="170" Gender="f" SexPref="straight" HairColor="brown" EyeColor="blue" Build="bbw" Fetishes="feet,smoking,anal,roleplay,dominant" Theme="toys,housewives,pornstar" Zodiac="aquarius" Ethnicity="caucasian" BodyMods="tattoos,piercings" CupSize="ddd/f" Language="en" PubicHair="bald" Audio="true" FreeChatAudio="true" Phone="false" LanguageISO="en"/>
        <Performer Id="29324295" Name="PennyArcade" Relevance="100" StreamType="live" PartyChat="1" PerfFlag="8931731" Updated="2016-12-20 18:27:19" GoldShow="0" PreGoldShow="0" Waist="28" Age="31" Bust="36" Hips="37" Height="64" Weight="127" Gender="f" SexPref="straight" HairColor="brown" EyeColor="brown" Build="average" Fetishes="feet,spankingpaddling,submissive,gagging" Theme="toys" Zodiac="cancer" Ethnicity="caucasian" BodyMods="tattoos" CupSize="b" Language="en" PubicHair="trimmed" Audio="true" FreeChatAudio="true" Phone="true" LanguageISO="en"/>
        <Performer Id="21612488" Name="Megan_Renee" Relevance="100" StreamType="live" PartyChat="0" PerfFlag="8407187" Updated="2014-04-18 21:39:40" GoldShow="0" PreGoldShow="0" Waist="25" Bust="34" Hips="35" Age="42" Height="63" Weight="106" Gender="f" SexPref="bi" HairColor="brown" EyeColor="brown" Build="petite" Fetishes="feet,underwear,roleplay,stockingsnylons" Zodiac="scorpio" Ethnicity="caucasian" CupSize="d" Language="en" PubicHair="bald" Theme="housewives" Audio="true" FreeChatAudio="true" Phone="false" LanguageISO="en"/>
        <Performer Id="35311343" Name="KendraEaine" Relevance="100" StreamType="live" PartyChat="0" PerfFlag="8407187" Updated="2016-09-20 06:53:18" GoldShow="0" PreGoldShow="0" Age="18" Waist="26" Bust="32" Hips="35" Height="65" Weight="100" Gender="f" SexPref="bi" HairColor="brown" EyeColor="green" Build="petite" Fetishes="feet,underwear,spankingpaddling,roleplay,deepthroat" Theme="toys" Zodiac="pisces" Ethnicity="caucasian" CupSize="a" Language="en" PubicHair="bald" Audio="true" FreeChatAudio="true" Phone="false" LanguageISO="en"/>
        <Performer Id="4173372" Name="ZoeyAndrews" Relevance="100" StreamType="live" PartyChat="1" PerfFlag="8931473" Updated="2017-09-14 17:02:06" GoldShow="0" PreGoldShow="0" Waist="36" Bust="38" Hips="40" Age="44" Height="70" Weight="200" Gender="f" SexPref="bi" HairColor="blond" EyeColor="blue" Build="bbw" Fetishes="feet,stockingsnylons,cuckold" Zodiac="libra" Ethnicity="caucasian" BodyMods="tattoos,piercings" CupSize="ddd/f" Language="en" PubicHair="bald" Theme="housewives,pornstar" Audio="true" FreeChatAudio="true" Phone="false" LanguageISO="en"/>
        <Performer Id="29899905" Name="Cassidy_Evans" Relevance="100" StreamType="live" PartyChat="1" PerfFlag="8931538" Updated="2017-02-10 18:57:21" GoldShow="0" PreGoldShow="0" Age="20" Waist="24" Bust="32" Hips="34" Height="63" Weight="120" Gender="f" SexPref="bi" HairColor="brown" EyeColor="green" Build="average" Fetishes="feet,voyeur,dominant,submissive,lactation" Theme="pregnancy,toys" Zodiac="virgo" Ethnicity="caucasian" CupSize="d" Language="en" PubicHair="trimmed" Audio="true" FreeChatAudio="true" Phone="false" LanguageISO="en"/>
    </AvailablePerformers>
    <Synopsis Units="English" Generated="1505509053" CacheTTL="0" ResponseTime="25798"/>
</SMLResult>
XML;

    public function testGetLivePerformers()
    {
        $memcached = $this->prophesize(\Memcached::class);
        $memcached->get('CamBuilder100LiveIds')->willReturn(false)->shouldBeCalledTimes(1);
        $memcached->set('CamBuilder100LiveIds', ['15314113', '5814046', '1883594', '4108253', '4923315', '29324295', '21612488', '35311343', '4173372', '29899905'], 120)->shouldBeCalledTimes(1);

        $body = $this->prophesize(StreamInterface::class);
        $body->getContents()->willReturn($this->responseXml)->shouldBeCalledTimes(1);

        $response = $this->prophesize(ResponseInterface::class);
        $response->getStatusCode()->willReturn(200)->shouldBeCalledTimes(1);
        $response->getBody()->willReturn($body)->shouldBeCalledTimes(1);

        $httpClient = $this->prophesize(Client::class);
        $httpClient->post('http://affiliate.streamate.com/SMLive/SMLResult.xml', [
            'headers' => ['Content-Type' => 'text/xml'],
            'body' => $this->queryXml,
            'timeout' => 10.0,
            'http_errors' => false,
        ])->willReturn($response)->shouldBeCalledTimes(1);

        $reader = new FeedsReader($memcached->reveal(), $httpClient->reveal());

        $result = $reader->getLivePerformers();

        $this->assertCount(10, $result);
    }

    public function testGetLivePerformersWhenAnExceptionOccurs()
    {
        $memcached = $this->prophesize(\Memcached::class);
        $memcached->get('CamBuilder100LiveIds')->willReturn(false)->shouldBeCalledTimes(1);
        $memcached->set('CamBuilder100LiveIds', [], 120)->shouldBeCalledTimes(1);

        $httpClient = $this->prophesize(Client::class);
        $httpClient->post('http://affiliate.streamate.com/SMLive/SMLResult.xml', [
            'headers' => ['Content-Type' => 'text/xml'],
            'body' => $this->queryXml,
            'timeout' => 10.0,
            'http_errors' => false,
        ])->willThrow(new \RuntimeException("It's a trap!"))->shouldBeCalledTimes(1);

        $reader = new FeedsReader($memcached->reveal(), $httpClient->reveal());

        $result = $reader->getLivePerformers();

        $this->assertEmpty($result);
    }
}
