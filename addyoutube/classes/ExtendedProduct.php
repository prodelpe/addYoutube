<?php

/**
 * @category  PrestaShop Module
 * @author    Pau Rodellino <prodelpe@gmail.com>
 * @copyright 2020 Pau Rodellino
 * @license   see file: LICENSE.txt
 */

class ExtendedProduct extends Product {

    public $addyoutube_lang;
    
    public function __construct($id_product = null, $full = false, $id_lang = null, $id_shop = null, \Context $context = null) {
        self::$definition['fields']['addyoutube_lang'] = [
            'type' => self::TYPE_STRING,
            'lang' => true,
            'required' => false, 
            'size' => 140
        ];
        parent::__construct($id_product, $full, $id_lang, $id_shop, $context);
    }
}
