<?php
/**
 * Plugin Name: Easy Form
 * Description: Plugin for form submission.
 * Version: 1.0.2
 * Author: Nitish Rajbongshi
 */
    if(!defined('ABSPATH')) {
        header('Location: /');
        exit();
    }

    // define the call back function
    function plugin_activation() {
        global $wpdb; // database global variable
        global $table_prefix; // geting the table prifix
        $wp_emp = $table_prefix.'emp';

        // query to create the table
        $sql = "
            CREATE TABLE IF NOT EXISTS `$wp_emp` (
                `id` INT NOT NULL AUTO_INCREMENT,
                `name` varchar(50) NOT NULL,
                `email` varchar(80) NOT NULL,
                `status` BOOLEAN NOT NULL,
                PRIMARY KEY(`id`)
            )
        ";

        $wpdb->query($sql); // execute the query

        // insert some dummy data whenever table is created by the plugin

        // taditional way to insert data
        // $sql = "
        //         INSERT INTO `$wp_emp`(`name`, `email`, `status`)
        //         VALUES ('Nitish', 'nitish@gmail.com', 1)
        // ";

        // $wpdb->query($sql); // execute the query to insert the dummy data

        // wordpress way to insert data through associate array
        // data to be inserted
        $data = array(
            "name" => "Nitish",
            "email" => "Nitish@gmail.com",
            "status" => 1
        );

        // insert data 
        $wpdb->insert($wp_emp, $data);
    }
    // takes to arguments
    // path and call back function
    register_activation_hook(__FILE__, 'plugin_activation');


    // function for plugin deactivation
    // whenever the plugin is deactivated this function is call automatically
    function plugin_deactivation() {
        global $wpdb, $table_prefix; // global variables

        $table = $table_prefix.'emp'; // get the table name 

        $sql = "TRUNCATE `$table`"; // query to be executed when the plugin is deactivate
        $wpdb->query($sql);  // execute the query
    }

    register_deactivation_hook(__FILE__, 'plugin_deactivation');


    // short code function
    // it can takes argument passed by the user 

    function call_easy_form($atts) {
        // convert to lowercase
        // user can type the attribute name in any case,  so 
        // to avoid conflict convert to a specific fixed case
        $atts = array_change_key_case($atts, CASE_LOWER);

        // default attributs
        // if user do not pass anything then
        $atts = shortcode_atts(array(
            "mgs" => "I am default attribute"
        ), $atts);

        // if parameter is passed by the user
        return "Function call ".$atts['mgs'];
    }

    // create a short code
    // parameter: Name of the short code, call back function
    add_shortcode('easy_form', 'call_easy_form');


    // shortcode for return html code
    // this is good when the html is short
    function return_my_html() {
        ob_start();
        ?>
            <h3>Hello world!</h3>
        <?php 
        $html = ob_get_clean();
        return $html;
    }
    add_shortcode('html_return', 'return_my_html');

    // shortcode for long html
    function long_html_code() {
        include 'custome_html.php';
    }
    add_shortcode('long_html', 'long_html_code');

    // shortcode for condition 
    // if type is given then another image is shown
    // otherwise another default image
    function my_condition($atts) {
        $atts = array_change_key_case((array)$atts, CASE_LOWER);

        $atts = shortcode_atts(array(
            'type' => 'image1'
        ), $atts);

        include $atts['type'].'.php';
    }
    add_shortcode('condition_check', 'my_condition');


    // proper way to include scripts
    function custome_scripts() {
        $path = plugins_url('js/main.js', __FILE__);
        $dep = array('jquery');
        $ver = filemtime(plugin_dir_path(__FILE__, 'js/main.js'));
        wp_enqueue_script("unique_script", $path, $dep, $ver, true);

        // include the style
        $path_style = plugins_url('css/main.css', __FILE__);
        $ver_style = filemtime(plugin_dir_path(__FILE__, 'css/main.css'));

        // add only in a particular page
        if(is_page('Custom Plugin')) {
            wp_enqueue_style('my_custom_style', $path_style, '', $ver_style);
        } 



        // include inline script
        $user_logedin = is_user_logged_in()? 1 : 0;
        wp_add_inline_script("unique_script", "var is_logedin = ". $user_logedin .";", "before");
    }

    add_action('wp_enqueue_scripts', 'custome_scripts');


    // fetch data from the database

    function fetch_emp_data() {
        global $wpdb, $table_prefix; // global variables
        $table = $table_prefix.'emp'; // get the table name

        // sql query to fetch all data
        $sql = "SELECT * FROM `$table`";
        // execute the sql query
        $result = $wpdb->get_results($sql);

        ob_start();
        ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    if(!empty($result)) {
                        foreach($result as $res) {
                            ?>
                            <tr>
                                <td><?php echo $res->id;?></td>
                                <td><?php echo $res->name;?></td>
                                <td><?php echo $res->email;?></td>
                                <td><?php echo $res->status;?></td>
                            </tr>
                            <?php
                        }
                    }
                ?>
            </tbody>
        </table>
        <?php
        $html = ob_get_clean();
        return $html;
    }
    add_shortcode('fetch_data_db', 'fetch_emp_data');

    // function to get all the posts
    function get_all_posts() {
        global $wpdb, $table_prefix; // global variables
        $table = $table_prefix.'posts'; // get the table name 

        // sql query to fetch all data
        $sql = "SELECT * FROM `$table` WHERE `post_status` = 'publish'";
        // execute the sql query
        $result = $wpdb->get_results($sql);

        ob_start();
        if(!empty($result)) {
            foreach($result as $res) {
                ?>
                <h4><?php echo $res->post_title;?></h4>
                <h5><?php echo $res->post_content;?></h5>
                <p> --<?php echo $res->post_date;?></p><br><br>
                <?php
            }
        }

        $html = ob_get_clean();
        return $html;

    }
    add_shortcode('get_the_posts', 'get_all_posts');

    // use of WP_Query method
    function use_of_wp_query() {
        
        $arg = array(
            "post_type" => "post"
        );

        // create an object
        $query = new WP_Query($arg);

        ob_start();
        // check for existance
        if($query->have_posts()):
        ?>  
            <ul>
            <?php
                while($query->have_posts()) {
                    $query->the_post();
                    echo "<li><h3>".get_the_title()."</h3></li>";
                    echo "<h6>".get_the_author()."</h6>";
                    echo "<p>".get_the_date()."</p>";
                    echo "<p>".get_the_content()."</p>";
                }
            ?>
            </ul>
            
        <?php
        endif;
        wp_reset_postdata();
        $html = ob_get_clean();
        return $html;
    }
    add_shortcode('use_wp_query', 'use_of_wp_query');
?>