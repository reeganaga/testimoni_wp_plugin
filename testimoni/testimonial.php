<?php
/*
Plugin Name: Testimonial Plugin 
Plugin URI: http://example.com
Description: Simple non-bloated WordPress Contact Form
Version: 1.0
Author: Rega Cahya Gumilang
Author URI: http://w3guy.com
*/
    //
    // the plugin code will go here..
    //


function html_form_code() {
    echo '<form action="' . esc_url( $_SERVER['REQUEST_URI'] ) . '" method="post">';
    echo '<p>';
    echo 'Name (required) <br />';
    echo '<input type="text" name="cf-name" pattern="[a-zA-Z0-9 ]+" value="' . ( isset( $_POST["cf-name"] ) ? esc_attr( $_POST["cf-name"] ) : '' ) . '" size="40" />';
    echo '</p>';
    echo '<p>';
    echo 'Email (required) <br />';
    echo '<input type="email" name="cf-email" value="' . ( isset( $_POST["cf-email"] ) ? esc_attr( $_POST["cf-email"] ) : '' ) . '" size="40" />';
    echo '</p>';
    echo '<p>';
    echo 'Phone Number <br />';
    echo '<input type="text" name="cf-phone_number" value="' . ( isset( $_POST["cf-phone_number"] ) ? esc_attr( $_POST["cf-phone_number"] ) : '' ) . '" size="40" />';
    echo '</p>';
    echo '<p>';
    echo 'Your Testimonial  <br />';
    echo '<textarea rows="10" cols="35" name="cf-testimoni">' . ( isset( $_POST["cf-testimoni"] ) ? esc_attr( $_POST["cf-testimoni"] ) : '' ) . '</textarea>';
    echo '</p>';
    echo '<p><input type="submit" name="cf-submitted" value="Send"/></p>';
    echo '</form>';
}


function deliver_mail() {

    // if the submit button is clicked, send the email
    if ( isset( $_POST['cf-submitted'] ) ) {

        // sanitize form values
        $name    = sanitize_text_field( $_POST["cf-name"] );
        $email   = sanitize_email( $_POST["cf-email"] );
        $phone_number = sanitize_text_field( $_POST["cf-phone_number"] );
        $testimoni = esc_textarea( $_POST["cf-testimoni"] );

        $data = array(
            'name'=>$name,
            'email'=>$email,
            'phone_number'=>$phone_number,
            'testimonial'=>$testimoni
            );
        //save to database
        global $wpdb;
        if ($wpdb->insert('testimonial',$data,'')) {
            echo "Your data has been recorded, thanks for participate";
        }else{
            echo "i'm sorry, there is problem to save data";
        }

    }
}

function delete_testimonial() {
    if (isset($_GET['id_testimonial']) && isset($_GET['action']) && ($_GET['action'] == 'delete_testimonial')) {
          global $wpdb;

          $wpdb->delete(
            "testimonial",
            [ 'id_testimonial' => $_GET['id_testimonial'] ]
          );
        echo "<script>alert('testimonial has been deleted')</script>";
        # code...
        // echo "<script>alert=".$_GET['id_testimonial']."</h1>";
        
    }

}



add_action( 'admin_init', 'delete_testimonial', 1 );

function cf_shortcode() {
    ob_start();
    deliver_mail();
    html_form_code();

    return ob_get_clean();
}

add_shortcode( 'testimonial', 'cf_shortcode' );

add_action( 'admin_menu', 'my_admin_menu' );

function my_admin_menu() {
    add_menu_page( 'Testimonial page', 'Testimonial', 'manage_options', 'testimoni/testimonial.php', 'myplguin_admin_page', 'dashicons-format-aside', 6  );
}

function myplguin_admin_page(){

    global $wpdb;

    $data=$wpdb->get_results('SELECT * FROM testimonial'); 
    ?>
    <div class="wrap">
        <h2>Here is Your Tesmonial</h2>
            <table class="wp-list-table widefat fixed striped pages">
                <thead>
                    <tr>
                        <th>id</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone Number</th>
                        <th>Testimonial</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($data as $row): ?>
                    <tr>
                        <td><?php echo $row->id_testimonial; ?></td>
                        <td><?php echo $row->name; ?></td>
                        <td><?php echo $row->email; ?></td>
                        <td><?php echo $row->phone_number; ?></td>
                        <td><?php echo $row->testimonial; ?></td>
                        <td><a class="button" onclick="return confirm('Are you sure to delete this testimonial?');" href="<?php echo admin_url('admin.php?page=testimoni/testimonial.php&id_testimonial='.$row->id_testimonial . '&action=delete_testimonial'); ?>">delete</a></td>
                    </tr>
                <?php endforeach ?>
                </tbody>
            </table>
    </div>
    <?php

}




/**
 * Adds Foo_Widget widget.
 */
class Foo_Widget extends WP_Widget {

    /**
     * Register widget with WordPress.
     */
    function __construct() {
        parent::__construct(
            'foo_widget', // Base ID
            __( 'Random testimonial', 'text_domain' ), // Name
            array( 'description' => __( 'Showing random testimonial', 'text_domain' ), ) // Args
        );
    }

    /**
     * Front-end display of widget.
     *
     * @see WP_Widget::widget()
     *
     * @param array $args     Widget arguments.
     * @param array $instance Saved values from database.
     */
    public function widget( $args, $instance ) {
        echo $args['before_widget'];
        if ( ! empty( $instance['title'] ) ) {
            echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
        }
        global $wpdb;
        // $data = $wpdb->get_row("SELECT * FROM testimonial order by rand",ARRAY_A);
        $data = $wpdb->get_row( "SELECT * FROM testimonial order by rand()", ARRAY_A );
        // print_r($data);
        echo "<h4>".$data['testimonial']."</h4>";
        echo "<i>".$data['name']."</i>";
        // echo __( esc_attr( 'Hello, World!' ), 'text_domain' );
        // echo $args['after_widget'];
    }

    /**
     * Back-end widget form.
     *
     * @see WP_Widget::form()
     *
     * @param array $instance Previously saved values from database.
     */
    public function form( $instance ) {
        $title = ! empty( $instance['title'] ) ? $instance['title'] : __( 'New title', 'text_domain' );
        ?>
        <p>
        <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( esc_attr( 'Title:' ) ); ?></label> 
        <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
        </p>
        <?php 
    }

    /**
     * Sanitize widget form values as they are saved.
     *
     * @see WP_Widget::update()
     *
     * @param array $new_instance Values just sent to be saved.
     * @param array $old_instance Previously saved values from database.
     *
     * @return array Updated safe values to be saved.
     */
    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

        return $instance;
    }

} // class Foo_Widget


// register Foo_Widget widget
function register_foo_widget() {
    register_widget( 'Foo_Widget' );
}
add_action( 'widgets_init', 'register_foo_widget' );

/**
 * Adds Testimonialthreelast widget.
 */
class last_testimoni_widget extends WP_Widget {

    /**
     * Register widget with WordPress.
     */
    function __construct() {
        parent::__construct(
            'last_testimoni_widget', // Base ID
            __( 'Testimonial three last', 'text_domain' ), // Name
            array( 'description' => __( 'Showing three last testimonial ', 'text_domain' ), ) // Args
        );
    }

    /**
     * Front-end display of widget.
     *
     * @see WP_Widget::widget()
     *
     * @param array $args     Widget arguments.
     * @param array $instance Saved values from database.
     */
    public function widget( $args, $instance ) {
        echo $args['before_widget'];
        if ( ! empty( $instance['title'] ) ) {
            echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
        }
        global $wpdb;
        // $data = $wpdb->get_row("SELECT * FROM testimonial order by rand",ARRAY_A);
        $data = $wpdb->get_results( "SELECT * FROM testimonial order by id_testimonial desc limit 0,3");
        // print_r($data);
        foreach ($data as $row) {
            echo "<h4>".$row->testimonial."</h4>";
            echo "<i>-".$row->name."</i>";
        }
        //
        // echo __( esc_attr( 'Hello, World!' ), 'text_domain' );
        // echo $args['after_widget'];
    }

    /**
     * Back-end widget form.
     *
     * @see WP_Widget::form()
     *
     * @param array $instance Previously saved values from database.
     */
    public function form( $instance ) {
        $title = ! empty( $instance['title'] ) ? $instance['title'] : __( 'New title', 'text_domain' );
        ?>
        <p>
        <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( esc_attr( 'Title:' ) ); ?></label> 
        <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
        </p>
        <?php 
    }

    /**
     * Sanitize widget form values as they are saved.
     *
     * @see WP_Widget::update()
     *
     * @param array $new_instance Values just sent to be saved.
     * @param array $old_instance Previously saved values from database.
     *
     * @return array Updated safe values to be saved.
     */
    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

        return $instance;
    }

} // class Foo_Widget


// register Foo_Widget widget
function register_last_testimoni_widget() {
    register_widget( 'last_testimoni_widget' );
}
add_action( 'widgets_init', 'register_last_testimoni_widget' );

?>

