<?php

class Elvanto {
    public static function give_elvanto_user_info($donor_data, $donor_location) {
        $auth_details = array('api_key' => '7zHxxY8vIqugentpKDwUAo0WYKuAQZKZ');
        $elvanto = new Elvanto_API($auth_details);

        $results = $elvanto->call('financial/transactions/create', $donor_data);

        $user_id = $results->transaction->person->id;

        $person_params = array(
            'id' => $user_id,
            'fields' => array(
                'locations' => array(
                    $donor_location
                )
            )
        );

        $add_location = $elvanto->call('people/edit', $person_params);

        return true;
    }
}