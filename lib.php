<?php

function returnPerson()
{
    global $firstNames,$lastNames,$streetNames,$townNames,$emailDomains,$diallingCodes;

        $streetTypes = array('Road', 'Drive','Place','Avenue');
        $dwellingTypes = array('Apartment','','Office','','','');
        $dataSets = array('firstNames','lastNames','streetNames','townNames','streetTypes','dwellingTypes','emailDomains');
        foreach ($dataSets as $thisData) {
            shuffle(${$thisData});
        }
        $person['first_name'] = $firstNames[array_rand($firstNames, 1)];
        $person['last_name'] = $lastNames[array_rand($lastNames, 1)];
        $person['address1'] = rand(1, 355).' '.$streetNames[array_rand($streetNames, 1)].' '.$streetTypes[array_rand($streetTypes, 1)];

        if ($dwellingTypes[array_rand($dwellingTypes, 1)] != '') {
            $person['address2'] = $dwellingTypes[array_rand($dwellingTypes, 1)].' '.rand(1, 31);
        } else {
            $person['address2'] = '';
        }
        $cityState = $townNames[array_rand($townNames, 1)];

        $person['city'] = $cityState[1];
        $person['state'] = $cityState[2];
        $person['zip'] = $cityState[0];
        $person['phone'] = $diallingCodes[$data['country']] . str_pad(rand(0, 999), 3, '0') . '-' . str_pad(rand(0, 999), 4, '0') . '-' . str_pad(rand(0, 9999), 4, '0');
        $person['email'] = strtolower($person['first_name']).'.'.strtolower($person['last_name']).'@'.$emailDomains[array_rand($emailDomains, 1)];
        $person['latitude'] = $cityState[5];
        $person['longitude'] = $cityState[6];

    return ($person);
}
