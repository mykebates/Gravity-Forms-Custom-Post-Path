<?php
 /*
Plugin Name: Custom Post Path For Gravity Forms
Plugin URI: 
Description: Configure your Graivty Forms to post to a secondary location.  Perfect for CRMs like Sales Force or Marketo
Version: 0.1
Author: Myke Bates - UpTrending
Author URI: http://uptrending.com/
Author Email: myke@uptrending.com
License:

  Copyright 2013 UpTrending (myke@uptrending.com)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as 
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
  
*/

if( !class_exists( 'gravityforms_custom_post_path' ) ) :

class gravityforms_custom_post_path {

	private static $instance;


	public static function singleton() 
	{
		if( !isset( self::$instance ) ) 
		{
			$class = __CLASS__;
			self::$instance = new $class();
		}
		return self::$instance;
	}

	/*
	 * Constructor
	 *
	 */
	private function __construct() 
	{
		add_action("gform_advanced_settings", array($this, "cpp_form_advanced_settings"), 10, 2);
		add_action("gform_editor_js", array($this, "cpp_form_editor_script"));
		add_filter('gform_tooltips', array($this, 'add_cpp_form_tooltips'), 0);
		add_action("gform_field_advanced_settings", array($this, "cpp_field_settings"), 10, 2);
		add_action("gform_editor_js", array($this, "editor_script"));
		add_filter('gform_tooltips', array($this, 'add_cpp_field_tooltips'));
		add_action("gform_post_submission", array($this, "send_post_content"), 10, 2);
	}


	public function cpp_form_advanced_settings($position, $form_id)
	{

	    if($position == 800){
	    ?>
	        <script type="text/javascript">
	    		function cppSaveFormSetting(value){
	    			form['cppFormField'] = value;
	    		}
	        </script>
	        <li class="cpp_setting field_setting">
	            <label for="field_admin_label">
	                <?php _e("Custom Post Path", "gravityforms"); ?>
	                <?php gform_tooltip("cpp_form_value") ?>
	            </label>
	            <input type="text" id="cpp_form_value" class="fieldwidth-3" onkeyup="cppSaveFormSetting(this.value);" />
	        </li>
	        <?php
	    }
	}


	public function cpp_form_editor_script()
	{
	    ?>
	    <script type='text/javascript'>
	        //adding setting to fields of type "text"
	        fieldSettings["text"] += ", .cpp_setting";


	        //binding to the load field settings event to initialize the value
	        jQuery(document).bind("gform_load_form_settings", function(event, field, form){
	            jQuery("#cpp_form_value").attr("value", field["cppFormField"]);
	        });
	    </script>
	    <?php
	}

	//Filter to add a new tooltip
	public function add_cpp_form_tooltips($tooltips)
	{
	   $tooltips["cpp_form_value"] = "<h6>Custom Post Path</h6>Set additional path for the form to post to after it is saved.";
	   return $tooltips;
	}


	public function cpp_field_settings($position, $form_id)
	{
	    //create settings on position 50 (right after Admin Label)
	    if($position == 50){
	        ?>
	        <li class="cpp_field_setting field_setting">
	            <label for="field_admin_label">
	                <?php _e("Field Name For Custom Post Path", "gravityforms"); ?>
	                <?php gform_tooltip("cpp_field_value") ?>
	            </label>
	            <input type="text" id="cpp_field_value" size="35" onkeyup="SetFieldProperty('cppField', this.value);" />
	        </li>
	        <?php
	    }
	}

	
	public function editor_script()
	{
	    ?>
	    <script type='text/javascript'>
	        (function(){

	        	jQuery.each(fieldSettings, function(index, value){
		        	fieldSettings[index] += ", .cpp_field_setting";
		        });

	        })()

	        //binding to the load field settings event to initialize the values
	        jQuery(document).bind("gform_load_field_settings", function(event, field, form){
	            //console.log(Math.floor(Math.random()*11));
	            jQuery("#cpp_field_value").attr("value", field["cppField"]);
	        });
	    </script>
	    <?php
	}

	
	public function add_cpp_field_tooltips($tooltips)
	{
	   $tooltips["cpp_field_value"] = "<h6>Field Name For Custom Post Path</h6>Set custom field name to pass to 3rd party form processor";
	   return $tooltips;
	}
	

	public function send_post_content($entry, $form)
	{
		$postPath = $form["cppFormField"];
		$fields = $form["fields"];
		$ourFields = array();

		if($postPath){
			// We have a custom post path set.
			// Not sure if all fields shoudl post or only ones with explicit values
			// Keeping explicit for meow
			
			foreach ($fields as $field) {
				if( $field["cppField"] ){
					
					$fieldID = $field["id"];
					$fieldValue = $entry["".$fieldID.""];
					$fieldName = $field["cppField"];

					// Check if this has multiple inputs(like a checkbox list)
					// This is the first shot at trying to tackle the issue of multiple value fields
					// Part of the problem is every service will handle this differently
					// Right now just crafting a csv string
					if(count($field["inputs"])){
						$listValues = "";

						foreach ($field["inputs"] as $input) {
							$id = $input["id"];
							if($entry["".$id.""]){
								$listValues .= $entry["".$id.""].", ";
							}
						}

						if($listValues)
							$ourFields[$fieldName] = substr($listValues, 0, -2);

					}else{
						// Not a multi input value.  Much easier to grab
						$ourFields[$fieldName] = $fieldValue;
					}
				}
			}
		}

		$this->postToUrl($postPath, $ourFields);
	}


	public function postToUrl($url, $data)
	{
		$fields = '';
		foreach($data as $key => $value) {
		$fields .= $key . '=' . $value . '&';
		}
		rtrim($fields, '&');

		$post = curl_init();

		curl_setopt($post, CURLOPT_URL, $url);
		curl_setopt($post, CURLOPT_POST, count($data));
		curl_setopt($post, CURLOPT_POSTFIELDS, $fields);
		curl_setopt($post, CURLOPT_RETURNTRANSFER, 1);

		$result = curl_exec($post);

		curl_close($post);
	}

}
endif; //class_exists 'gravityforms_custom_post_path'

//Instantiate
gravityforms_custom_post_path::singleton();