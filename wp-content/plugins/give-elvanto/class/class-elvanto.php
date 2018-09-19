<?php

class Elvanto {
    public static function give_elvanto_user_info($donor_data) {
        $donor_transaction = $donor_data[0];
        $donor_date = $donor_data[1];
        $donor_donation = $donor_data[2];
        $donor_donor_fname = $donor_data[3];
        $donor_donor_lname = $donor_data[4];
        $donor_email = $donor_data[5];
        $donor_location = $donor_data[6];
        $donor_category = $donor_data[7];

        $auth_details = array('api_key' => give_get_option('elvanto_api_key'));
        $category_id = '';

        if ($donor_category == 'Tithe' || $donor_category == 'Tithes') {
            $category_id = give_get_option('elvanto_tithe_id');
        } else if ($donor_category == 'Offering') {
            $category_id = give_get_option('elvanto_offering_id');
        } else if ($donor_category == 'Building Fund') {
            $category_id = give_get_option('elvanto_building_id');
        } else if ($donor_category == 'Network & Missions') {
            $category_id = give_get_option('elvanto_missions_id');
        } else if ($donor_category == 'Community Care Foundation' || $donor_category == 'New Life Community Care') {
            $category_id = give_get_option('elvanto_community_id');
        } else if ($donor_category == 'The Joseph Project') {
            $category_id = give_get_option('elvanto_joseph_id');
        } else if ($donor_category == 'New Life Metro') {
            $category_id = give_get_option('elvanto_metro_id');
        } else if ($donor_category == 'New Life Churches') {
            $category_id = give_get_option('elvanto_churces_id');
        } else if ($donor_category == 'Other') {
            $category_id = give_get_option('elvanto_other_id');
        }

        $elvanto = new Elvanto_API($auth_details);

        $donor_data = array(
            'person' =>  array (
                'firstname' => $donor_donor_fname,
                'lastname' => $donor_donor_lname,
                'email' => $donor_email
            ),
            'transaction_date' => $donor_date,
            'amounts' => array (
                array (
                    'category_id' => $category_id,
                    'total' => $donor_donation,
                    'memo' => 'Transaction ID: ' . $donor_transaction
                )
            )
        );

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

    /**
     * Give Elvanto Admin Settings
     */

    public function give_elvanto_add_settings ($settings) {
        $siteslug = str_replace('/','',str_replace('/main/','',$GLOBALS['path']));

        if ($siteslug == 'thefort') { // Sub Accounts for The Fort
            $this->give_settings = array(
                array(
                    'name' => '<strong>' . __('Elvanto Settings', 'give-elvanto') . '</strong>',
                    'desc' => '<hr>',
                    'type' => 'give_title',
                    'id' => 'give_title_elvanto',
                ),
                array(
                    'id' => 'elvanto_api_key',
                    'name' => esc_html__('API Key', 'give-elvanto'),
                    'desc' => esc_html__('Please enter your Elvanto API key.', 'give-elvanto'),
                    'type' => 'text'
                ),
                array(
                    'id' => 'elvanto_tithe_id',
                    'name' => esc_html__('Tithe Category ID', 'give-elvanto'),
                    'desc' => esc_html__('Please enter your Elvanto Tithe Category ID.', 'give-elvanto'),
                    'type' => 'text'
                ),
                array(
                    'id' => 'elvanto_offering_id',
                    'name' => esc_html__('Offering Category ID', 'give-elvanto'),
                    'desc' => esc_html__('Please enter your Elvanto Offering Category ID.', 'give-elvanto'),
                    'type' => 'text'
                ),
                array(
                    'id' => 'elvanto_building_id',
                    'name' => esc_html__('Building Fund Category ID', 'give-elvanto'),
                    'desc' => esc_html__('Please enter your Elvanto Building Fund Category ID.', 'give-elvanto'),
                    'type' => 'text'
                ),
                array(
                    'id' => 'elvanto_missions_id',
                    'name' => esc_html__('Network & Missions Category ID', 'give-elvanto'),
                    'desc' => esc_html__('Please enter your Elvanto Network & Missions Category ID.', 'give-elvanto'),
                    'type' => 'text'
                ),
                array(
                    'id' => 'elvanto_community_id',
                    'name' => esc_html__('Community Care Foundation Category ID', 'give-elvanto'),
                    'desc' => esc_html__('Please enter your Elvanto Community Care Foundation Category ID.', 'give-elvanto'),
                    'type' => 'text'
                ),
                array(
                    'id' => 'elvanto_joseph_id',
                    'name' => esc_html__('The Joseph Project Category ID', 'give-elvanto'),
                    'desc' => esc_html__('Please enter your Elvanto Joseph Project Category ID.', 'give-elvanto'),
                    'type' => 'text'
                ),
            );
        } else { // Sub Accounts for Alabang and Main
            $this->give_settings = array(
                array(
                    'name' => '<strong>' . __('Elvanto Settings', 'give-elvanto') . '</strong>',
                    'desc' => '<hr>',
                    'type' => 'give_title',
                    'id' => 'give_title_elvanto',
                ),
                array(
                    'id' => 'elvanto_api_key',
                    'name' => esc_html__('API Key', 'give-elvanto'),
                    'desc' => esc_html__('Please enter your Elvanto API key.', 'give-elvanto'),
                    'type' => 'text'
                ),
                array(
                    'id' => 'elvanto_tithe_id',
                    'name' => esc_html__('Tithes Category ID', 'give-elvanto'),
                    'desc' => esc_html__('Please enter your Elvanto Tithes Category ID.', 'give-elvanto'),
                    'type' => 'text'
                ),
                array(
                    'id' => 'elvanto_offering_id',
                    'name' => esc_html__('Offering Category ID', 'give-elvanto'),
                    'desc' => esc_html__('Please enter your Elvanto Offering Category ID.', 'give-elvanto'),
                    'type' => 'text'
                ),
                array(
                    'id' => 'elvanto_building_id',
                    'name' => esc_html__('Building Fund Category ID', 'give-elvanto'),
                    'desc' => esc_html__('Please enter your Elvanto Building Fund Category ID.', 'give-elvanto'),
                    'type' => 'text'
                ),
                array(
                    'id' => 'elvanto_community_id',
                    'name' => esc_html__('New Life Community Care Category ID', 'give-elvanto'),
                    'desc' => esc_html__('Please enter your Elvanto New Life Community Care Category ID.', 'give-elvanto'),
                    'type' => 'text'
                ),
                array(
                    'id' => 'elvanto_metro_id',
                    'name' => esc_html__('New Life Metro Category ID', 'give-elvanto'),
                    'desc' => esc_html__('Please enter your Elvanto New Life Metro Category ID.', 'give-elvanto'),
                    'type' => 'text'
                ),
                array(
                    'id' => 'elvanto_churces_id',
                    'name' => esc_html__('New Life Churches Category ID', 'give-elvanto'),
                    'desc' => esc_html__('Please enter your Elvanto New Life Churches Category ID.', 'give-elvanto'),
                    'type' => 'text'
                ),
                array(
                    'id' => 'elvanto_other_id',
                    'name' => esc_html__('Other Category ID', 'give-elvanto'),
                    'desc' => esc_html__('Please enter your Elvanto Other Category ID.', 'give-elvanto'),
                    'type' => 'text'
                ),
            );
        }

        return array_merge( $settings, $this->give_settings );
    }
}