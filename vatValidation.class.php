<?php

class vatValidation {

    const WSDL = "http://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl";

    private $_valid_country_code = array(
        'AT', 'BE', 'BG,', 'CY', 'CZ', 'DE', 'DK', 'EE', 'EL', 'ES', 'FI', 'FR', 'GB', 'HR', 'HU', 'IE', 'IT',
        'LT', 'LU', 'LV', 'MT', 'NL', 'PL', 'PT', 'RO', 'SE', 'SI', 'SK'
    );
    private $_client = null;
    private $_options = array(
        'debug' => false,
    );
    private $_valid = false;
    private $_data = array();

    public function __construct($options = array()) {

        foreach ($options as $option => $value) {
            $this->_options[$option] = $value;
        }

        if (!class_exists('SoapClient')) {
            throw new Exception('The Soap library has to be installed and enabled');
        }

        try {
            $this->_client = new SoapClient(self::WSDL, array('trace' => true));
        } catch (Exception $e) {
            $this->trace('Vat Translation Error', $e->getMessage());
        }
    }

    /**
     * Verify the vat number against the online vies database.
     * It <b>doesn't</b> verify that the country code is between the whitelist in $this->_valid_Country_code.
     * @param type $countryCode         2 characters country code
     * @param type $vatNumber           VAT number
     * @return boolean
     */
    public function check($countryCode, $vatNumber) {

        $rs = $this->_client->checkVat(array('countryCode' => $countryCode, 'vatNumber' => $vatNumber));

        if ($this->isDebug()) {
            $this->trace('Web Service result', $this->_client->__getLastResponse());
        }

        if ($rs->valid) {
            $this->_valid = true;
            $this->_data = array(
                'name' => $this->cleanUpString($rs->name),
                'address' => $this->cleanUpString($rs->address),
            );
            return true;
        } else {
            $this->_valid = false;
            $this->_data = array();
            return false;
        }
    }

    public function isValid() {
        return $this->_valid;
    }

    public function getName() {
        return $this->_data['name'];
    }

    public function getAddress() {
        return $this->_data['address'];
    }

    public function isDebug() {
        return ($this->_options['debug'] === true);
    }

    private function trace($title, $body) {
        echo '<h2>TRACE: ' . $title . '</h2><pre>' . htmlentities($body) . '</pre>';
    }

    private function cleanUpString($string) {
        return ucwords(strtolower(preg_replace('/\s+/', ' ', $string)));
    }

    /**
     * Returns the list of valid country codes
     * @return array
     */
    public function getValidCountryCodes() {
        return $this->_valid_country_code;
    }

}
