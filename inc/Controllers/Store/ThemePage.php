<?php

require_once('google-auth/check_user.php');
require_once('inc/class/Theme.php');
require_once('inc/class/ThemeUtil.php');
require_once('inc/class/ThemeTrans.php');
require_once('inc/class/ThemeTag.php');

require_once 'inc/class/TargetCountryUtil.php';


class ThemePage extends Webpage {

	public function __default() {
		$perpage = 20;

		if (isset($_GET['page'])) {
			$page = $_GET['page'];
		} else {
			$page = 1;
		}
		$perpage = isset($_GET['offset'])?intval($_GET['offset']):20;
		$search = isset($_GET['search'])?$_GET['search']:'';
		$type = isset($_GET['type'])? intval($_GET['type']):999;
		$order = !empty($_GET['order'])?$_GET['order']:'id';

		$count = ThemeUtil::getTotalThemes($type, $search);
		$end = ceil($count/$perpage);

		debug("before debug:");
		$themes = ThemeUtil::getThemes($page, $perpage, $type, $order, $search);
		$categories_arr = ThemeUtil::getThemeCategories();
		foreach ($categories_arr as $key => $value) {
			$categories[$value['cat_id']] = $value['category'];
		}
		$tag_groups = TagUtil::getGroups();
		$this->smarty->assign('tag_groups', $tag_groups);
		debug("after debug:");
		debug("theme count:" . count($themes));
		$this->smarty->assign('categories', $categories);

		$type = $type != 999 ? '&type='.$type : '';
		$order = $order != '`id` DESC' ? '&order='.$order : '';
		$search = !empty($search)?'&search='.$search:'';
		$keyword = !empty($search)?substr($search, 8):'';
		$perpage = '&offset='.$perpage;
		$this->smarty->assign('count', $count);
		$this->smarty->assign('keyword', $keyword);
		$this->smarty->assign('search', $search);
		$this->smarty->assign('type', $type);
		$this->smarty->assign('order', $order);
		$this->smarty->assign('token', $this->getCSRFToken());
		$this->smarty->assign('themes', $themes);
		$this->smarty->assign('page', $page);
		$this->smarty->assign('offset', $perpage);
		$this->smarty->assign('end', $end);
		return $this->smarty->fetch('../tpl/www/home/theme.tpl');
	}

	public function requiresUser() {
		return false;
	}

	public function add() {
		$this->disableHeaderAndFooter();

		$package_name = $_POST['package_name'];
		$name = $_POST['name'];
		$project_name = $_POST['project_name'];
		$support_language = $_POST['support_language'];
		$paid = $_POST['paid'];
		$icon = $_POST['icon'];
		$type = $_POST['type'];//select
		$featured = $_POST['featured'];
		$weight = $_POST['weight'];
		$is_new = $_POST['is_new'] == 1 ? 1 : 0;
		$promotion_image = $_POST['promotion_image'];
		$promotion_image_url = $_POST['promotion_image_url'];
		$preview_image_urls = $_POST['preview_image_urls'];
		$preview_gif_image_url = $_POST['preview_gif_image_url'];
		$description = $_POST['description'];
		$market_link = $_POST['market_link'];
		$version = $_POST['version'];
		$downloads = $_POST['downloads'];
		$rating_score = $_POST['rating_score'];
		$rating_count = $_POST['rating_count'];
		$min_api_level = $_POST['min_api_level'];
		$max_api_level = $_POST['max_api_level'];
		$help_content = $_POST['help_content'];
		$error_content = $_POST['error_content'];
		$feature = $_POST['feature'];
		$restriction = $_POST['restriction'];
		$regex = $_POST['regex'];
		$regex_index = $_POST['regex_index'];
		$product_id = $_POST['product_id'];
		$is_ourproduct = $_POST['is_ourproduct'];
		$status = ($_POST['status'] == 0) ? 0 : 1;
		$token = $_POST['token'];
		$promotion_link = $_POST['promotion_link'];
		$category = $_POST['category']?:0;
		$download_url = $_POST['download_url'];
		$app_promotion_image = $_POST['app_promotion_image'];
		$promotion_icon = $_POST['promotion_icon'];
		$partner_url = $_POST['partner_url'];
		$partner_icon = $_POST['partner_icon'];
		$transparent_preview_image_4_1 = $_POST['transparent_preview_image_4_1'];
		$transparent_preview_image_4_2_clock = $_POST['transparent_preview_image_4_2_clock'];
		$transparent_preview_image_4_2_forecast = $_POST['transparent_preview_image_4_2_forecast'];
		$wallpaper = $_POST['wallpaper'];
		$wallpaper_preview = $_POST['wallpaper_preview'];
		$min_app_version = $_POST['min_app_version'];
		$raise_public_type = $_POST['raise_public_type'];
		$raise_public_switch = $_POST['raise_public_switch'];
		$raise_public_current = $_POST['raise_public_current'];
		$raise_public_target1 = $_POST['raise_public_target1'];
		$raise_public_target2 = $_POST['raise_public_target2'];
		$tags = $_POST['tags'];
		$country_type = $_POST['country_type'];


		if(!isset($package_name) || !isset($name) || !isset($paid) || !isset($featured) || !isset($weight) || !isset($preview_image_urls) || !isset($preview_gif_image_url)
			|| !isset($description) || !isset($market_link) || !isset($version) || !isset($downloads) || !isset($rating_score) || !isset($rating_count)
			|| !isset($max_api_level) || !isset($min_api_level) || !isset($regex) || !isset($regex_index) || !isset($feature) || !isset($regex_index)
			|| !isset($product_id) || !isset($is_ourproduct) || !isset($status) || !isset($promotion_icon) || !isset($partner_icon) || !isset($partner_url)
			|| !isset($wallpaper) || !isset($wallpaper_preview) || !isset($min_app_version)
			|| !isset($raise_public_switch) || !isset($raise_public_current) || !isset($raise_public_target1) || !isset($raise_public_target2) || !isset($raise_public_type)
			|| !isset($transparent_preview_image_4_1) || !isset($transparent_preview_image_4_2_clock) || !isset($transparent_preview_image_4_2_forecast)) {

			return json_encode(array('status'=>'error','error_msg'=>'Invalid params.'));
		}

		if(!$this->checkCSRFToken($token)){
			return json_encode(array('status'=>'error','error_msg'=>'Session timeout,please refresh the page.'));
		}

		$theme = new Theme();
		$theme->package_name = $package_name;
		$theme->name = $name;
		$theme->project_name = $project_name;
		$theme->support_language = $support_language;
		$theme->category = $category;
		$theme->type = $type;
		$theme->icon = $icon;
		$theme->paid = $paid;
		$theme->featured = $featured;
		$theme->weight = $weight;
		$theme->is_new = $is_new;
		$theme->promotion_image = $promotion_image;
		$theme->promotion_image_url = $promotion_image_url;
		$theme->preview_image_urls = $preview_image_urls;
		$theme->preview_gif_image_url = $preview_gif_image_url;
		$theme->description = $description;
		$theme->market_link = $market_link;
		$theme->version = $version;
		$theme->downloads = $downloads;
		$theme->rating_score = $rating_score;
		$theme->rating_count = $rating_count;
		$theme->min_api_level = $min_api_level;
		$theme->max_api_level = $max_api_level;
		$theme->help_content = $help_content;
		$theme->error_content = $error_content;
		$theme->feature = $feature;
		$theme->restriction = $restriction;
		$theme->regex = $regex;
		$theme->regex_index = $regex_index;
		$theme->product_id = $product_id;
		$theme->is_ourproduct = $is_ourproduct;
		$theme->status = $status;
		$theme->promotion_link = $promotion_link;
		$theme->download_url = $download_url;
		$theme->app_promotion_image = $app_promotion_image;
		$theme->promotion_icon = $promotion_icon;
		$theme->partner_url = $partner_url;
		$theme->partner_icon = $partner_icon;
		$theme->transparent_preview_image_4_1 = $transparent_preview_image_4_1;
		$theme->transparent_preview_image_4_2_clock = $transparent_preview_image_4_2_clock;
		$theme->transparent_preview_image_4_2_forecast = $transparent_preview_image_4_2_forecast;
		$theme->wallpaper = $wallpaper;
		$theme->wallpaper_preview = $wallpaper_preview;
		$theme->min_app_version = $min_app_version;
		$theme->raise_public_type = $raise_public_type;
		$theme->raise_public_current = $raise_public_current;
		$theme->raise_public_switch = $raise_public_switch;
		$theme->raise_public_target1 = $raise_public_target1;
		$theme->raise_public_target2 = $raise_public_target2;
		$theme->tags = $tags;
		$theme->country_type = $country_type;
		$theme->update_time = date('Y-m-d H:i:s');

		debug("before debug:");
		$insert_id = $theme->add();
		debug("after debug");

		return json_encode(array('status'=>'success','theme_id'=>$insert_id));
	}

	public function delete() {
		$this->disableHeaderAndFooter();

		$id_list = trim($_POST['id_list']);
		$token = $_POST['token'];

		if (is_numeric($id_list)) {
			$id_arr[0] = $id_list;
		} else {
			$id_arr = explode(" ",$id_list);
		}

		if (!isset($id_arr) || !isset($token)) {
			return json_encode(array('status'=>'error','error_msg'=>'Invalid params.'));
		}

		if (!$this->checkCSRFToken($token)) {
			return json_encode(array('status'=>'error','error_msg'=>'Session timeout,please refresh the page.'));
		}

		foreach ($id_arr as $id) {
			$theme = new Theme($id);
			$theme->delete();

			$theme_id = $id;
			$theme_trans = new ThemeTrans($theme_id);
			$theme_trans->deleteTranslation();

			TagUtil::deleteRelationByTheme($theme_id);

			$a = TargetCountryUtil::getTargetCountriesByTid($id);
			foreach ($a as $c) {
				$tc = new TargetCountry($c['id']);
				$tc->delete();
			}
		}

		return json_encode(array('status'=>'success'));
	}

	public function update() {
		$this->disableHeaderAndFooter();

		$id = $_POST['id'];
		$package_name = $_POST['package_name'];
		$name = $_POST['name'];
		$project_name = $_POST['project_name'];
		$support_language = $_POST['support_language'];
		$type = intval($_POST['type']);
		$icon = $_POST['icon'];
		$paid = $_POST['paid'];
		$featured = $_POST['featured'];
		$weight = $_POST['weight'];
		$is_new = $_POST['is_new'] == 1 ? 1 : 0;
		$promotion_image = $_POST['promotion_image'];
		$promotion_image_url = $_POST['promotion_image_url'];
		$preview_image_urls = $_POST['preview_image_urls'];
		$preview_gif_image_url = $_POST['preview_gif_image_url'];
		$description = $_POST['description'];
		$market_link = $_POST['market_link'];
		$version = $_POST['version'];
		$downloads = $_POST['downloads'];
		$rating_score = $_POST['rating_score'];
		$rating_count = $_POST['rating_count'];
		$min_api_level = $_POST['min_api_level'];
		$max_api_level = $_POST['max_api_level'];
		$help_content = $_POST['help_content'];
		$error_content = $_POST['error_content'];
		$feature = $_POST['feature'];
		$restriction = $_POST['restriction'];
		$regex = $_POST['regex'];
		$regex_index = $_POST['regex_index'];
		$product_id = $_POST['product_id'];
		$is_ourproduct = $_POST['is_ourproduct'];
		$status = ($_POST['status'] == 0) ? 0 : 1;
		$token = $_POST['token'];
		$promotion_link = $_POST['promotion_link'];
		$category = $_POST['category']?:0;
		$download_url = $_POST['download_url'];
		$app_promotion_image = $_POST['app_promotion_image'];
		$promotion_icon = $_POST['promotion_icon'];
		$partner_url = $_POST['partner_url'];
		$partner_icon = $_POST['partner_icon'];
		$transparent_preview_image_4_1 = $_POST['transparent_preview_image_4_1'];
		$transparent_preview_image_4_2_clock = $_POST['transparent_preview_image_4_2_clock'];
		$transparent_preview_image_4_2_forecast = $_POST['transparent_preview_image_4_2_forecast'];
		$wallpaper_preview = $_POST['wallpaper_preview'];
		$wallpaper = $_POST['wallpaper'];
		$min_app_version = $_POST['min_app_version'];
		$raise_public_type = $_POST['raise_public_type'];
		$raise_public_switch = $_POST['raise_public_switch'];
		$raise_public_current = $_POST['raise_public_current'];
		$raise_public_target1 = $_POST['raise_public_target1'];
		$raise_public_target2 = $_POST['raise_public_target2'];
		$tags = $_POST['tags'];
		$country_type = $_POST['country_type'];



		if(!isset($id) || !isset($token) ||!isset($package_name) || !isset($name) || !isset($paid) || !isset($featured) || !isset($weight) || !isset($preview_image_urls) || !isset($preview_gif_image_url) || !isset($description)
			|| !isset($market_link) || !isset($version) || !isset($downloads) || !isset($rating_score) || !isset($rating_count)
			|| !isset($max_api_level) || !isset($min_api_level) || !isset($regex) || !isset($regex_index) || !isset($feature) || !isset($regex_index)
			|| !isset($product_id) || !isset($is_ourproduct) || !isset($status) || !isset($promotion_icon) || !isset($partner_url) || !isset($partner_icon)
			|| !isset($wallpaper) || !isset($wallpaper_preview) || !isset($min_app_version)
			|| !isset($raise_public_switch) || !isset($raise_public_current) || !isset($raise_public_target1) || !isset($raise_public_target2) || !isset($raise_public_type)
			|| !isset($transparent_preview_image_4_1) || !isset($transparent_preview_image_4_2_clock) || !isset($transparent_preview_image_4_2_forecast)) {

			return json_encode(array('status'=>'error','error_msg'=>'Invalid params.'));
		}


		if (!$this->checkCSRFToken($token)) {
			return json_encode(array('status'=>'error','error_msg'=>'Session time out,please refresh the page.'));
		}
		debug($wallpaper_preview . "+++111+");
		$theme = new Theme($id);
		$theme->package_name = $package_name;
		$theme->name = $name;
		$theme->project_name = $project_name;
		$theme->support_language = $support_language;
		$theme->type = $type;
		$theme->icon = $icon;
		$theme->paid = $paid;
		$theme->featured = $featured;
		$theme->weight = $weight;
		$theme->is_new = $is_new;
		$theme->promotion_image = $promotion_image;
		$theme->promotion_image_url = $promotion_image_url;
		$theme->preview_image_urls = $preview_image_urls;
		$theme->preview_gif_image_url = $preview_gif_image_url;
		$theme->description = $description;
		$theme->market_link = $market_link;
		$theme->version = $version;
		$theme->downloads = $downloads;
		$theme->rating_score = $rating_score;
		$theme->rating_count = $rating_count;
		$theme->min_api_level = $min_api_level;
		$theme->max_api_level = $max_api_level;
		$theme->help_content = $help_content;
		$theme->error_content = $error_content;
		$theme->feature = $feature;
		$theme->restriction = $restriction;
		$theme->regex = $regex;
		$theme->regex_index = $regex_index;
		$theme->product_id = $product_id;
		$theme->is_ourproduct = $is_ourproduct;
		$theme->status = $status;
		$theme->promotion_link = $promotion_link;
		$theme->category = $category;
		$theme->download_url = $download_url;
		$theme->app_promotion_image = $app_promotion_image;
		$theme->promotion_icon = $promotion_icon;
		$theme->partner_url = $partner_url;
		$theme->partner_icon = $partner_icon;
		$theme->transparent_preview_image_4_1 = $transparent_preview_image_4_1;
		$theme->transparent_preview_image_4_2_clock = $transparent_preview_image_4_2_clock;
		$theme->transparent_preview_image_4_2_forecast = $transparent_preview_image_4_2_forecast;
		$theme->wallpaper = $wallpaper;
		$theme->wallpaper_preview = $wallpaper_preview;
		$theme->min_app_version = $min_app_version;
		$theme->raise_public_type = $raise_public_type;
		$theme->raise_public_current = $raise_public_current;
		$theme->raise_public_switch = $raise_public_switch;
		$theme->raise_public_target1 = $raise_public_target1;
		$theme->raise_public_target2 = $raise_public_target2;
		$theme->tags = $tags;
		$theme->country_type = $country_type;
		$theme->update_time = date('Y-m-d H:i:s');

		debug($theme->wallpaper_preview . "+++222+");

		debug("before update");
		$theme->update();
		debug("after update");


		return json_encode(array('status'=>'success'));
	}

	public function checkExit() {
		$this->disableHeaderAndFooter();

		$package_name = trim($_POST['package_name']);
		if (empty($package_name)) {
			return json_encode(array('status'=>'error', 'error_msg'=>'package name is empty!'));
		}
		$total = ThemeUtil::checkPkgName($package_name);
		debug("total: ".$total);

		if (0 == $total) {
			return json_encode(array('status'=>'success'));
		} else {
			return json_encode(array('status'=>'error','error_msg'=>'package name already exists!'));
		}
	}
}
