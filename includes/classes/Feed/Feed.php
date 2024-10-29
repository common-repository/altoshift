<?php

namespace Altoshift\WordPress\Feed;

defined('ABSPATH') or die;

use Altoshift\WordPress\AltoshiftWordpressPlugin;

class Feed
{
    private static $_instance = null;

    private $_settings = [];

    public $header = [];
    public $products = [];
    public $pages = [];
    private $contents;
    public $categories = [];
    private $type = 'all';
    private $flagAddFieldsPost = 0;
    private $flagAddFieldsPage = 0;

    public static $predefinedFields = array(
        "ID",
        "post_title",
        "post_content",
        "category_ids",
        "post_status",
        "post_type",
        "post_date",
        "published_date_gmt",
    );

    public $additionalFields = array();


    public static function registerUrls()
    {
        add_feed('altoshift', function () {
            \Altoshift\WordPress\Feed\Feed::getInstance()
                ->init()
                ->generate()
                ->render();
        });
    }

    public static function getInstance()
    {
        if (self::$_instance === null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function fetchSettings()
    {
        $this->_settings = array_merge($this->_settings, array(
            'altoshift_feed_price_export' => get_option('altoshift_feed_price_export', 'no'),
            'altoshift_page_feed' => get_option('altoshift_page_feed', 'no'),
            'altoshift_post_feed' => get_option('altoshift_post_feed', 'no'),
            'altoshift_feed_password_protected' => get_option('altoshift_feed_password_protected', 'no'),
            'altoshift_feed_password' => get_option('altoshift_feed_password', ''),
        ));
    }

    public function init()
    {
        $this->fetchSettings();
        return $this;
    }

    public function generate()
    {
        if (!$this->isAuthorized()) {
            return $this;
        }
        if (isset($_GET['type']) && !empty($_GET['type'])) {
            $this->type = sanitize_text_field($_GET['type']);
        }

        $this->header['title'] = get_bloginfo('name');
        $this->header['link'] = get_site_url();
        $this->header['description'] = sanitize_text_field(get_bloginfo('description'));

        $this->loadProducts();
        $this->loadCategories();

        return $this;
    }

    private function loadCategories()
    {
        global $wp_version;
        $productCategories = [];
        if (version_compare($wp_version, '4.5.0', '>=')) {
            $args = array(
                'taxonomy'   => "category",
                'hide_empty' => 0,
            );
            $productCategories = get_terms($args);
        } else {
            $args = array(
                'hide_empty' => 0,
            );

            $productCategories = get_terms( 'category', $args );
        }

        $this->categories = $productCategories;

        return $productCategories;
    }

    private function renderProductCategories($categories, $element = 'categories')
    {
        echo '<'.$element.'>';
        foreach ($categories as $category)
        {
            $this->createElement('category', $category);
        }
        echo '</'.$element.'>';
    }

    private function renderTags($tags, $element = 'tags')
    {
        // echo '<'.$element.'>';
        $tag_arr = [];
        foreach ($tags as $tag)
        {
            $tag_arr[] = $tag->name;
            // $this->createElement('tag', $tag->name);
        }
        $tag_name = implode(",", $tag_arr);
        $this->createElement('tags', $tag_name);
        // echo '</'.$element.'>';
    }

    private function renderPostAuthor($tag, $post) {
        $post_author = get_user_by('ID', $post);
        $author_name = $post_author->display_name;
        $this->createElement($tag, $author_name);
    }

    public function render()
    {
        if (!$this->isAuthorized()) {
            return $this;
        }

        header('Content-Type: application/xml');

        echo '<?xml version="1.0" encoding="UTF-8"?><rss version="2.0"><channel>';

        foreach ($this->header as $headerName => $headerValue) {
            $this->createElement($headerName, $headerValue);
        }

        echo '<allFields>';
        foreach($this->additionalFields as $field)
        {
            $this->createElement('field', $field);
        }
        echo '</allFields>';


        foreach($this->categories as $category)
        {
            echo '<category>';
            $this->createElement('id', $category->term_id);
            $this->createElement('name', $category->name);
            $this->createElement('description', $category->description);
            $this->createElement('parent', $category->parent);
            $this->createElement('count', $category->count);
            echo '</category>';
        }

        if ($this->type === 'all' || $this->type === 'post') {
            foreach ($this->products as $product) {
                echo '<post>';
                foreach ($product as $field => $value) {
                    if ($field == 'categoryIds' || $field === 'categoryTree') {
                        $this->renderProductCategories($value, $field);
                        continue;
                    }
                    if ($field == 'tags') {
                        $this->renderTags($value, $field);
                        continue;
                    }
                    if ($field == 'post_author') {
                        $this->renderPostAuthor('post_author',$value);
                        continue;
                    }
                    $this->createElement($field, $value);
                }
                echo '</post>';
            }
        }

        if ($this->type === 'all' || $this->type === 'page') {
            foreach ($this->pages as $page) {
                echo '<page>';
                foreach ($page as $field => $value) {
                    if ($field == 'post_author') {
                        $this->renderPostAuthor('post_author',$value);
                        continue;
                    }
                    $this->createElement($field, $value);
                }
                echo '</page>';
            }
        }

        echo '</channel></rss>';


        return $this;
    }

    private function getCategoryTree($catid) {
        $result = [];
        while ($catid) {
            $cat = get_category($catid);
            $catid = $cat->category_parent;
            $result[] = $cat->cat_ID;
        }
        return $result;
    }

    private function getCategoriesTree($arrCatIds) {
        $result = [];
        foreach ($arrCatIds as $catId) {
            $result = array_merge($result,$this->getCategoryTree($catId));
        }
        return $result;
    }

    private function getProductCategories($postId)
    {
        $ids = array();
        $terms = get_the_terms($postId, 'category');
        foreach ($terms as $term) {
            $ids[] = $term->term_id;
        }

        return $ids;
    }

    private function formatProducts($post) 
    {
        $productCategories = $this->getProductCategories($post->ID);
        $imageId = get_post_thumbnail_id($post->ID);
        $imageLinks = wp_get_attachment_image_src($imageId, 'full');
        $tags = wp_get_post_tags($post->ID);
        $costumeFields = ["ID", "post_title", "post_content", "post_status", "post_type", "post_date", "post_date_gmt", "categoryTree"];
        $categoryTree = $this->getCategoriesTree($productCategories);

        foreach (array_keys(get_object_vars($post)) as $key) {
            if (in_array($key, $costumeFields))
                continue;
            $productItem[$key] = $post->{$key};
        }

        $productItem = array_merge(array(
            'id' => $post->ID,
            'link' => get_permalink($post),
            'title' => $post->post_title,
            'description' => $post->post_content,
            'image_link' => is_array($imageLinks) && count($imageLinks) ? $imageLinks[0] : '',
            'status' => $post->post_status,
            'type' => $post->post_type,
            'categoryIds' => $productCategories,
            'categoryTree' => $categoryTree,
            'tags' => $tags,
            'published_date' => $post->post_date,
            'published_date_gmt' => $post->post_date_gmt,
        ), $productItem);
        
        return $productItem;
    }

    private function formatPages($page) {
        $result = array(
            'id' => $page->ID,
            'link' => get_permalink($page),
            'title' => $page->post_title,
            'description' => $page->post_content,
            'status' => $page->post_status,
            'type' => $page->post_type,
            'published_date' => $page->post_date,
            'published_date_gmt' => $page->post_date_gmt,
            'categoryIds' => "",
            'categoryTree' => "",
        );
        $costumeFields = ["ID", "post_title", "post_content", "post_status", "post_type", "post_date", "post_date_gmt"];
        foreach (array_keys(get_object_vars($page)) as $key) {
            if (in_array($key, $costumeFields))
                continue;
            $result[$key] = $page->${key};
        }
        return $result;
    }

    public function loadProducts()
    {
        $moreFields = array();
        if (isset($_GET['more_fields']) && !empty($_GET['more_fields'])) {
            try {
                $moreFields = explode(",", sanitize_text_field($_GET['more_fields']));
            } catch(Exception $e) {
                $moreFields = array();
            }
        }
        if (($this->type === 'all' || $this->type === 'post') && $this->_settings['altoshift_post_feed'] === 'yes') {
            $this->products = [];
            $args = array(
                'post_type' => 'post',
                'post_status' => 'publish',
                'ignore_sticky_posts' => 1,
                'cache_results' => false,
                'update_post_meta_cache' => false,
                'update_post_term_cache' => false,
                'orderby' => 'ID',
                'order' => 'ASC',
                'posts_per_page' => -1,
            );
            if (isset($_GET['limit']) && !empty($_GET['limit']) && is_numeric(sanitize_text_field($_GET['limit'])) ) {
                $args['posts_per_page'] = sanitize_text_field($_GET['limit']);
            }
            if (isset($_GET['skip']) && !empty($_GET['skip']) && is_numeric(sanitize_text_field($_GET['skip'])) ) {
                $args['offset'] = sanitize_text_field($_GET['skip']);
            }
            $query = new \WP_Query($args);
            foreach ( $query->posts as $post) {
                $product = $this->formatProducts($post);
                $tempProduct = array();

                try {
                    // if (!count($this->additionalFields)) {
                    if ($this->flagAddFieldsPost == 0){
                        $this->additionalFields = $this->getAdditionalFields($product);
                        $this->flagAddFieldsPost = 1;
                    }
                } catch (Exception $e) {

                }
                $tempProduct["id"] = $product["id"];
                foreach($moreFields as $field) {
                    try {
                        $tempProduct[$field] = $product[$field];
                    } catch (Exception $e) {
                        unset($tempProduct[$field]);
                    }
                }

                $this->products[] = $tempProduct;
            }
        }
        if (($this->type === 'all' || $this->type === 'page') && $this->_settings['altoshift_page_feed'] === 'yes') {
            $this->contents = get_pages();
            foreach ( $this->contents as $page) {
                $tempPage = $this->formatPages($page);
                if ($this->flagAddFieldsPage == 0) {
                    $this->additionalFields = array_unique(array_merge($this->additionalFields, $this->getAdditionalFields($tempPage)));
                    $this->flagAddFieldsPage = 1;
                }
                $pageItem["id"] = $tempPage["id"];

                foreach($moreFields as $field) {
                    try {
                        $pageItem[$field] = $tempPage[$field];
                    } catch (Exception $e) {
                        unset($pageItem[$field]);
                    }
                }

                $this->pages[] = $pageItem;
            }
        }
    }

    public function isProtected()
    {
        return $this->_settings['altoshift_feed_password_protected'] === 'yes' && strlen($this->_settings['altoshift_feed_password']) > 0;
    }

    public function isAuthorized()
    {
        return !$this->isProtected() || $this->_settings['altoshift_feed_password'] === sanitize_text_field($_GET['secret']);
    }

    private function createElement($name, $value)
    {
        echo '<' . $name . '>';
        $this->wrapCdata($value);
        echo '</' . $name . '>';
    }
    
    private function wrapCdata($value)
    {
        echo "<![CDATA[$value]]>";
    }

    private function getAdditionalFields($obj) {
        $objFields = array_keys($obj);
        return $objFields;
    }
}