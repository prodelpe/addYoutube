<?php

/**
 * @category  PrestaShop Module
 * @author    Pau Rodellino <prodelpe@gmail.com>
 * @copyright 2020 Pau Rodellino
 * @license   see file: LICENSE.txt
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

require_once 'classes/ExtendedProduct.php';

class AddYoutube extends Module {

    public function __construct() {
        $this->name = 'addyoutube';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Pau Rodellino';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = [
            'min' => '1.6',
            'max' => _PS_VERSION_
        ];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Add YouToube');
        $this->description = $this->l('Adds a YouTube video to your product and shows it in product page.');
        $this->confirmUninstall = $this->l('This will delete all your YouTube URLs.');
    }

    /**
     * Install
     * @return boolean
     */
    public function install() {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        if (!parent::install() ||
                !$this->_installSql() ||
                //Action when sacing/updating product
                !$this->registerHook('actionProductUpdate') ||
                //CSS files
                !$this->registerHook('displayHeader') ||
                !$this->registerHook('displayBackOfficeHeader') ||
                //Show Product on front
                !$this->registerHook('displayFooterProduct') ||
                !$this->registerHook('displayProductAdditionalInfo') ||
                !$this->registerHook('displayProductActions') ||
                !$this->registerHook('displayProductExtraContent') ||
                !$this->registerHook('displayProductAdditionalInfo') ||
                !$this->registerHook('displayProductListReviews') ||
                !$this->registerHook('displayProductPageDrawer') ||
                !$this->registerHook('displayProductPriceBlock') ||
                !$this->registerHook('displayReassurance') ||
                //Show options in Admin Product
                !$this->registerHook('displayAdminProductsMainStepLeftColumnMiddle')
        ) {
            return false;
        }

        return true;
    }

    /**
     * Uninstall
     * @return type
     */
    public function uninstall() {
        return parent::uninstall() && $this->_unInstallSql();
    }

    /**
     * Adds custom column on install
     * @return boolean
     */
    protected function _installSql() {
        $sqlInstallLang = "ALTER TABLE " . _DB_PREFIX_ . "product_lang "
                . "ADD addyoutube_lang VARCHAR(255) NULL";

        $returnSqlLang = Db::getInstance()->execute($sqlInstallLang);

        return $returnSqlLang;
    }

    /**
     * Delete custom column on uninstall
     * @return boolean
     */
    protected function _unInstallSql() {
        $sqlInstallLang = "ALTER TABLE " . _DB_PREFIX_ . "product_lang "
                . "DROP addyoutube_lang";

        $returnSqlLang = Db::getInstance()->execute($sqlInstallLang);

        return $returnSqlLang;
    }

    /**
     * Shows YouTube URL and Video Preview in Admin Product
     * https://victor-rodenas.com/2018/02/19/anadir-campos-a-los-productos-en-prestashop-1-7/
     * @param type $params
     * @return string
     */
    public function hookDisplayAdminProductsMainStepLeftColumnMiddle($params) {
        $product = new ExtendedProduct($params['id_product']); //Current product in page
        $languages = Language::getLanguages($active); //All active languagea in shop
        $video_link_current_language = $product->addyoutube_lang[$this->context->employee->id_lang]; //Video URL of current language
        $this->context->smarty->assign(
                array(
                    'languages' => $languages,
                    'addyoutube_lang' => $product->addyoutube_lang,
                    'addyoutube_lang_embed' => $this->getYoutubeEmbedUrl($video_link_current_language),
                    'default_language' => $this->context->employee->id_lang
                )
        );

        return $this->display(__FILE__, 'youtubefield.tpl');
    }

    /**
     * Shows YouTube video in front product page
     * @param type $params
     * @return boolean
     */
    public function hookDisplayProductAdditionalInfo($params) {
        $currentLangId = $this->context->language->id;
        $product = new ExtendedProduct(Tools::getValue('id_product', false, $currentLangId));
        $videoURL = $product->addyoutube_lang[$currentLangId];

        //We only return template if video is set
        if (is_null($videoURL) || !isset($videoURL) || empty($videoURL)) {
            return false;
        } else {
            $embedCode = $this->getYoutubeEmbedUrl($videoURL);
            $this->context->smarty->assign('embedCode', $embedCode);

            return $this->display(__FILE__, 'displayvideo.tpl');
        }
    }

    /**
     * Transforma una URL de Youtube en codi embed per incrustar
     * @param type $url
     * @return type
     */
    function getYoutubeEmbedUrl($url) {
        $shortUrlRegex = '/youtu.be\/([a-zA-Z0-9_]+)\??/i';
        $longUrlRegex = '/youtube.com\/((?:embed)|(?:watch))((?:\?v\=)|(?:\/))(\w+)/i';

        if (preg_match($longUrlRegex, $url, $matches)) {
            $youtube_id = $matches[count($matches) - 1];
        }

        if (preg_match($shortUrlRegex, $url, $matches)) {
            $youtube_id = $matches[count($matches) - 1];
        }

        return '<iframe src="https://www.youtube.com/embed/' . $youtube_id . '" frameborder="0" allowfullscreen class="video"></iframe>';
    }

    /**
     * Adds CSS to Product page
     * https://devdocs.prestashop.com/1.7/themes/getting-started/asset-management/
     */
    public function hookDisplayHeader() {
        // Only on product page
        if ('product' === $this->context->controller->php_self) {
            $this->context->controller->addCSS($this->_path . 'views/css/addyoutube-front.css', 'all');
        }
    }

    /**
     * Adds CSS to Admin Product page
     */
    public function hookDisplayBackOfficeHeader() {
        if ('AdminProducts' === $this->context->controller->php_self) {
            $this->context->controller->addCSS($this->_path . 'views/css/addyoutube-back.css', 'all');
        }
    }

    /**
     * Sends YouTube URL in the product form to validate
     * @param type $params
     */
    public function hookActionProductUpdate() {
        $currentLangId = $this->context->language->id;
        $product = new ExtendedProduct(Tools::getValue('id_product', false, $currentLangId));
        $videoURL = $product->addyoutube_lang[$currentLangId];
        if ($videoURL != '' && !$this->isValidYoutubeURL($videoURL)) {
            $this->errors[] = Tools::displayError($this->l('There is an error in your YouTube URL'));
        }
    }

    /**
     * Checks if a YouTube URL is correct
     * @param type $videoURL
     * @return boolean
     */
    function isValidYoutubeURL($videoURL) {
        $regex_pattern = "/(youtube.com|youtu.be)\/(watch)?(\?v=)?(\S+)?/";

        if (preg_match($regex_pattern, $videoURL)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Rest of the hooks
     */
    function hookDisplayFooterProduct($params) {
        
    }

    function hookDisplayProductActions($params) {
        
    }

    function hookDisplayProductExtraContent($params) {
        
    }

    function hookDisplayProductListReviews($params) {
        
    }

    function hookDisplayProductPageDrawer($params) {
        
    }

    function hookDisplayProductPriceBlock($params) {
        
    }

    function hookDisplayReassurance($params) {
        
    }

}
