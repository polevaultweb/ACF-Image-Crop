<?php

class acf_field_image_crop extends acf_field_image {


    /*
    *  __construct
    *
    *  This function will setup the field type data
    *
    *  @type    function
    *  @date    5/03/2014
    *  @since   5.0.0
    *
    *  @param   n/a
    *  @return  n/a
    */

    function __construct() {

        /*
        *  name (string) Single word, no spaces. Underscores allowed
        */

        $this->name = 'image_crop';


        /*
        *  label (string) Multiple words, can include spaces, visible when selecting a field type
        */

        $this->label = __('Image with user-crop', 'acf-image_crop');


        /*
        *  category (string) basic | content | choice | relational | jquery | layout | CUSTOM GROUP NAME
        */

        $this->category = 'content';


        /*
        *  defaults (array) Array of default settings which are merged into the field object. These are used later in settings
        */

        $this->defaults = array(
            'force_crop' => 'no',
            'crop_type' => 'hard',
            'preview_size' => 'medium',
            'save_format' => 'id',
            'save_in_media_library' => 'yes',
            'target_size' => 'thumbnail',
            'library' => 'all'
        );

        // add ajax action to be able to retrieve full image size via javascript
        add_action( 'wp_ajax_acf_image_crop_get_image_size', array( &$this, 'crop_get_image_size' ) );
        add_action( 'wp_ajax_acf_image_crop_perform_crop', array( &$this, 'perform_crop' ) );


        /*
        *  l10n (array) Array of strings that are used in JavaScript. This allows JS strings to be translated in PHP and loaded via:
        *  var message = acf._e('image_crop', 'error');
        */

        $this->l10n = array(
            'error' => __('Error! Please enter a higher value', 'acf-image_crop'),
        );


        // do not delete!
        acf_field::__construct();
        //parent::__construct();

    }


    // AJAX handler for retieving full image dimensions from ID
    public function crop_get_image_size()
    {
        $img = wp_get_attachment_image_src( $_POST['image_id'], 'full');
        if($img){
            echo json_encode( array(
                    'url' => $img[0],
                    'width' => $img[1],
                    'height' => $img[2]
                ) );
            }
        exit;
    }


    /*
    *  render_field_settings()
    *
    *  Create extra settings for your field. These are visible when editing a field
    *
    *  @type    action
    *  @since   3.6
    *  @date    23/01/13
    *
    *  @param   $field (array) the $field being edited
    *  @return  n/a
    */

    function render_field_settings( $field ) {

        /*
        *  acf_render_field_setting
        *
        *  This function will create a setting for your field. Simply pass the $field parameter and an array of field settings.
        *  The array of settings does not require a `value` or `prefix`; These settings are found from the $field array.
        *
        *  More than one setting can be added by copy/paste the above code.
        *  Please note that you must also have a matching $defaults value for the field name (font_size)
        */

         // crop_type
        acf_render_field_setting( $field, array(
            'label'         => __('Crop type','acf-image_crop'),
            'instructions'  => __('Select the type of crop the user should perform','acf-image_crop'),
            'type'          => 'select',
            'name'          => 'crop_type',
            'layout'        => 'horizontal',
            'class'         => 'crop-type-select',
            'choices'       => array(
                'hard'          => __('Hard crop', 'acf-image_crop'),
                'min'           => __('Minimal dimensions', 'acf-image_crop')
            )
        ));

        // target_size
        $sizes = acf_get_image_sizes();
        $sizes['custom'] = __('Custom size', 'acf-image_crop');
        acf_render_field_setting( $field, array(
            'label'         => __('Target size','acf-image_crop'),
            'instructions'  => __('Select the target size for this field','acf-image_crop'),
            'type'          => 'select',
            'name'          => 'target_size',
            'class'         => 'target-size-select',
            'choices'       =>  $sizes
        ));

        // width - conditional: target_size == 'custom'
        acf_render_field_setting( $field, array(
            'label'         => __('Custom target width','acf-image_crop'),
            'instructions'  => __('Leave blank for no restriction (does not work with hard crop option)','acf-image_crop'),
            'type'          => 'number',
            'name'          => 'width',
            'class'         => 'custom-target-width custom-target-dimension'
        ));

        // height - conditional: target_size == 'custom'
        acf_render_field_setting( $field, array(
            'label'         => __('Custom target height','acf-image_crop'),
            'instructions'  => __('Leave blank for no restriction (does not work with hard crop option)','acf-image_crop'),
            'type'          => 'number',
            'name'          => 'height',
            'class'         => 'custom-target-height custom-target-dimension'
        ));

        // preview_size
        acf_render_field_setting( $field, array(
            'label'         => __('Preview size','acf-image_crop'),
            'instructions'  => __('Select the preview size for this field','acf-image_crop'),
            'type'          => 'select',
            'name'          => 'preview_size',
            'choices'       =>  acf_get_image_sizes()
        ));

        // force_crop
        acf_render_field_setting( $field, array(
            'label'         => __('Force crop','acf-image_crop'),
            'instructions'  => __('Force the user to crop the image as soon at it is selected','acf-image_crop'),
            'type'          => 'radio',
            'layout'        => 'horizontal',
            'name'          => 'force_crop',
            'choices'       =>  array('yes' => 'Yes', 'no' => 'No')
        ));

        // save_in_media_library
        acf_render_field_setting( $field, array(
            'label'         => __('Save cropped image to media library','acf-image_crop'),
            'instructions'  => __('If the cropped image is not saved in the media library, "Image URL" is the only available return value.','acf-image_crop'),
            'type'          => 'radio',
            'layout'        => 'horizontal',
            'name'          => 'save_in_media_library',
            'class'         => 'save-in-media-library-select',
            'choices'       =>  array('yes' => 'Yes', 'no' => 'No')
        ));


        // return_format
        acf_render_field_setting( $field, array(
            'label'         => __('Return Value','acf-image_crop'),
            'instructions'  => __('Specify the returned value on front end','acf-image_crop'),
            'type'          => 'radio',
            'name'          => 'save_format',
            'layout'        => 'horizontal',
            'class'         =>  'return-value-select',
            'choices'       => array(
                'url'           => __("Image URL",'acf'),
                'id'            => __("Image ID",'acf'),
                'object'         => __("Image Object",'acf')
            )
        ));

        // library
        acf_render_field_setting( $field, array(
            'label'         => __('Library','acf'),
            'instructions'  => __('Limit the media library choice','acf'),
            'type'          => 'radio',
            'name'          => 'library',
            'layout'        => 'horizontal',
            'choices'       => array(
                'all'           => __('All', 'acf'),
                'uploadedTo'    => __('Uploaded to post', 'acf')
            )
        ));

    }



    /*
    *  render_field()
    *
    *  Create the HTML interface for your field
    *
    *  @param   $field (array) the $field being rendered
    *
    *  @type    action
    *  @since   3.6
    *  @date    23/01/13
    *
    *  @param   $field (array) the $field being edited
    *  @return  n/a
    */

    function render_field( $field ) {


       // enqueue
        acf_enqueue_uploader();

        // get data from value
        //$data = json_decode($field['value']);
        $imageData = $this->get_image_data($field);

        $url = '';
        $orignialImage = null;

        if($imageData->original_image){
            $originalImage = wp_get_attachment_image_src($imageData->original_image, 'full');
            $url = $imageData->preview_image_url;
        }

        $width = 0;
        $height = 0;

        if($field['target_size'] == 'custom'){
            $width = $field['width'];
            $height = $field['height'];
        }
        else{
            global $_wp_additional_image_sizes;
            $s = $field['target_size'];
            if (isset($_wp_additional_image_sizes[$s])) {
                $width = intval($_wp_additional_image_sizes[$s]['width']);
                $height = intval($_wp_additional_image_sizes[$s]['height']);
            } else {
                $width = get_option($s.'_size_w');
                $height = get_option($s.'_size_h');
            }
        }

        // vars
        $div_atts = array(
            'class'                 => 'acf-image-uploader acf-cf acf-image-crop',
            'data-crop_type'        => $field['crop_type'],
            'data-target_size'      => $field['target_size'],
            'data-width'            => $width,
            'data-height'           => $height,
            'data-force_crop'       => $field['force_crop'] == 'yes' ? 'true' : 'false',
            'data-save_in_media_library' => $field['save_in_media_library'] == 'yes' ? 'true' : 'false',
            'data-save_format'      => $field['save_format'],
            'data-preview_size'     => $field['preview_size'],
            'data-library'          => $field['library']
        );
        $input_atts = array(
            'type'                  => 'hidden',
            'name'                  => $field['name'],
            'value'                 => htmlspecialchars($field['value']),
            'data-name'             => 'value-id',
            'data-original-image'   => $imageData->original_image,
            'data-cropped-image'    => json_encode($imageData->cropped_image),
            'class'                 => 'acf-image-value'
        );

        // has value?
        if($imageData->original_image){
            $url = $imageData->preview_image_url;
            $div_atts['class'] .= ' has-value';
        }

?>
<div <?php acf_esc_attr_e( $div_atts ); ?>>
    <div class="acf-hidden">
        <input <?php acf_esc_attr_e( $input_atts ); ?>/>
    </div>
    <div class="view show-if-value acf-soh">
        <ul class="acf-hl acf-soh-target">
            <li><a class="acf-icon dark" data-name="edit-button" href="#"><i class="acf-sprite-edit"></i></a></li>
            <li><a class="acf-icon dark" data-name="remove-button" href="#"><i class="acf-sprite-delete"></i></a></li>
        </ul>
        <img data-name="value-url" src="<?php echo $url; ?>" alt=""/>
        <div class="crop-section">
            <div class="crop-stage">
                <div class="crop-action">
                    <h4>Crop the image</h4>
                <?php if ($imageData->original_image ): ?>
                    <img class="crop-image" src="<?php echo $imageData->original_image_url ?>" data-width="<?php echo $imageData->original_image_width ?>" data-height="<?php echo $imageData->original_image_height ?>" alt="">
                <?php endif ?>
                </div>
                <div class="crop-preview">
                    <h4>Preview</h4>
                    <div class="preview"></div>
                    <div class="crop-controls">
                        <a href="#" class="button button-large cancel-crop-button">Cancel</a> <a href="#" class="button button-large button-primary perform-crop-button">Crop!</a>
                    </div>
                </div>
            </div>
            <a href="#" class="button button-large init-crop-button">Crop</a>
        </div>
    </div>
    <div class="view hide-if-value">
        <p><?php _e('No image selected','acf'); ?> <a data-name="add-button" class="acf-button" href="#"><?php _e('Add Image','acf'); ?></a></p>
    </div>
</div>
<?php

    }

    /***
    * Parses the field value into a consistent data object
    ****/
    function get_image_data($field){
        $imageData = new stdClass();
        $imageData->original_image = '';
        $imageData->original_image_width = '';
        $imageData->original_image_height = '';
        $imageData->cropped_image = '';
        $imageData->original_image_url = '';
        $imageData->preview_image_url = '';
        $imageData->image_url = '';

        if($field['value'] == ''){
            // Field has not yet been saved or is an empty image field
            return $imageData;
        }

        $data = json_decode($field['value']);

        if(! is_object($data)){
            // Field was saved as a regular image field
            $imageAtts = wp_get_attachment_image_src($field['value'], 'full');
            $imageData->original_image = $field['value'];
            $imageData->original_image_width = $imageAtts[1];
            $imageData->original_image_height = $imageAtts[2];
            $imageData->preview_image_url = $this->get_image_src($field['value'], $field['preview_size']);
            $imageData->image_url = $this->get_image_src($field['value'], 'full');
            return $imageData;
        }

        if( !is_numeric($data->original_image) )
        {
            // The field has been saved, but has no image
            return $imageData;
        }

        // By now, we have at least a saved original image
        $imageAtts = wp_get_attachment_image_src($data->original_image, 'full');
        $imageData->original_image = $data->original_image;
        $imageData->original_image_width = $imageAtts[1];
        $imageData->original_image_height = $imageAtts[2];
        $imageData->original_image_url = $this->get_image_src($data->original_image, 'full');

        // Set defaults to original image
        $imageData->image_url = $this->get_image_src($data->original_image, 'full');
        $imageData->preview_image_url = $this->get_image_src($data->original_image, $field['preview_size']);

        // Check if there is a cropped version and set appropriate attributes
        if(is_numeric($data->cropped_image)){
            // Cropped image was saved to media library ans has an ID
            $imageData->cropped_image = $data->cropped_image;
            $imageData->image_url = $this->get_image_src($data->cropped_image, 'full');
            $imageData->preview_image_url = $this->get_image_src($data->cropped_image, $field['preview_size']);
        }
        elseif(is_object($data->cropped_image)){
            // Cropped image was not saved to media library and is only stored by its URL
            $imageData->cropped_image = $data->cropped_image;

            // Generate appropriate URLs
            $mediaDir = wp_upload_dir();
            $imageData->image_url = $mediaDir['baseurl'] . '/' .  $data->cropped_image->image;
            $imageData->preview_image_url = $mediaDir['baseurl'] . '/' . $data->cropped_image->preview;
        }
        return $imageData;
    }


    /*
    *  input_admin_enqueue_scripts()
    *
    *  This action is called in the admin_enqueue_scripts action on the edit screen where your field is created.
    *  Use this action to add CSS + JavaScript to assist your render_field() action.
    *
    *  @type    action (admin_enqueue_scripts)
    *  @since   3.6
    *  @date    23/01/13
    *
    *  @param   n/a
    *  @return  n/a
    */



    function input_admin_enqueue_scripts() {

        $dir = plugin_dir_url( __FILE__ );


        // // register & include JS
        // wp_register_script( 'acf-input-image_crop', "{$dir}js/input.js" );
        // wp_enqueue_script('acf-input-image_crop');


        // // register & include CSS
        // wp_register_style( 'acf-input-image_crop', "{$dir}css/input.css" );
        // wp_enqueue_style('acf-input-image_crop');

        // register acf scripts
        wp_register_script('acf-input-image_crop', "{$dir}js/input.js", array('acf-input', 'imgareaselect'));

        wp_register_style('acf-input-image_crop', "{$dir}css/input.css", array('acf-input'));
        //wp_register_script( 'jcrop', includes_url( 'js/jcrop/jquery.Jcrop.min.css' ));


        // scripts
        wp_enqueue_script(array(
                'acf-input-image_crop'
        ));

        //wp_localize_script( 'acf-input-image_crop', 'ajax', array('nonce' => wp_create_nonce('acf_nonce')) );

        // styles
        wp_enqueue_style(array(
                'acf-input-image_crop',
                'imgareaselect'
        ));


    }

    function perform_crop(){
        $targetWidth = $_POST['target_width'];
        $targetHeight = $_POST['target_height'];
        $saveToMediaLibrary = isset($_POST['save_to_media_library']) && $_POST['save_to_media_library'] == 'yes';
        $imageData = $this->generate_cropped_image($_POST['id'], $_POST['x1'], $_POST['x2'], $_POST['y1'], $_POST['y2'], $targetWidth, $targetHeight, $saveToMediaLibrary, $_POST['preview_size']);
        // $previewUrl = wp_get_attachment_image_src( $id, $_POST['preview_size']);
        // $fullUrl = wp_get_attachment_image_src( $id, 'full');
        echo json_encode($imageData);
        die();
    }

    function generate_cropped_image($id, $x1, $x2, $y1, $y2, $targetW, $targetH, $saveToMediaLibrary, $previewSize){//$id, $x1, $x2, $y$, $y2, $targetW, $targetH){
        require_once ABSPATH . "/wp-admin/includes/file.php";
        require_once ABSPATH . "/wp-admin/includes/image.php";

        // Create the variable that will hold the new image data
        $imageData = array();

        // Fetch media library info
        $mediaDir = wp_upload_dir();

        // Get original image info
        $originalImageData = wp_get_attachment_metadata($id);

        // Get image editor from original image path to crop the image
        $image = wp_get_image_editor( $mediaDir['basedir'] . '/' . $originalImageData['file'] );

        // Crop the image using the provided measurements
        $image->crop($x1, $y1, $x2 - $x1, $y2 - $y1, $targetW, $targetH);

        // Retrieve original filename and seperate it from its file extension
        $originalFileName = explode('.', basename($originalImageData['file']));

        // Generate new base filename
        $targetFileName = $originalFileName[0] . '_' . $targetW . 'x' . $targetH . '_acf_cropped'  . '.' . $originalFileName[1];

        // Generate target path new file using existing media library
        $targetFilePath = $mediaDir['path'] . '/' . wp_unique_filename( $mediaDir['path'], $targetFileName);

        // Get the relative path to save as the actual image url
        $targetRelativePath = str_replace($mediaDir['basedir'] . '/', '', $targetFilePath);

        // Save the image to the target path
        if(is_wp_error($image->save($targetFilePath))){
            // There was an error saving the image
            //TODO handle it
        }

        // If file should be saved to media library, create an attachment for it at get the new attachment ID
        if($saveToMediaLibrary){
            // Generate attachment from created file

            // Get the filetype
            $wp_filetype = wp_check_filetype(basename($targetFilePath), null );
            $attachment = array(
                 'guid' => $targetFilePath,
                 'post_mime_type' => $wp_filetype['type'],
                 'post_title' => preg_replace('/\.[^.]+$/', '', basename($targetFilePath)),
                 'post_content' => '',
                 'post_status' => 'inherit'
            );
            $attachmentId = wp_insert_attachment( $attachment, $targetFilePath);
            $attachmentData = wp_generate_attachment_metadata( $attachmentId, $targetFilePath );
            wp_update_attachment_metadata( $attachmentId, $attachmentData );

            // Add the id to the imageData-array
            $imageData['value'] = $attachmentId;

            // Add the image url
            $imageUrlObject = wp_get_attachment_image_src( $attachmentId, 'full');
            $imageData['url'] = $imageUrlObject[0];

            // Add the preview url as well
            $previewUrlObject = wp_get_attachment_image_src( $attachmentId, $previewSize);
            $imageData['preview_url'] = $previewUrlObject[0];
        }
        // Else we need to return the actual path of the cropped image
        else{
            // Add the image url to the imageData-array
            $imageData['value'] = array('image' => $targetRelativePath);
            $imageData['url'] = $mediaDir['baseurl'] . '/' . $targetRelativePath;

            // Get preview size dimensions
            global $_wp_additional_image_sizes;
            $previewWidth = 0;
            $previewHeight = 0;
            $crop = 0;
            if (isset($_wp_additional_image_sizes[$previewSize])) {
                $previewWidth = intval($_wp_additional_image_sizes[$previewSize]['width']);
                $previewHeight = intval($_wp_additional_image_sizes[$previewSize]['height']);
                $crop = $_wp_additional_image_sizes[$previewSize]['crop'];
            } else {
                $previewWidth = get_option($previewSize.'_size_w');
                $previewHeight = get_option($previewSize.'_size_h');
                $crop = get_option($previewSize.'_crop');
            }

            // Generate preview file path
            $previewFilePath = $mediaDir['path'] . '/' . wp_unique_filename( $mediaDir['path'], 'preview_' . $targetFileName);
            $previewRelativePath = str_replace($mediaDir['basedir'] . '/', '', $previewFilePath);

            // Get image editor from cropped image
            $croppedImage = wp_get_image_editor( $targetFilePath );
            $croppedImage->resize($previewWidth, $previewHeight, $crop);

            // Save the preview
            $croppedImage->save($previewFilePath);

            // Add the preview url
            $imageData['preview_url'] = $mediaDir['baseurl'] . '/' . $previewRelativePath;
            $imageData['value']['preview'] = $previewRelativePath;
        }
        return $imageData;
    }

    function get_image_src($id, $size = 'thumbnail'){
        $atts = wp_get_attachment_image_src( $id, $size);
        return $atts[0];
    }

    function getAbsoluteImageUrl($relativeUrl){
        $mediaDir = wp_upload_dir();
        return $mediaDir['baseurl'] . '/' .  $relativeUrl;
    }




    /*
    *  input_admin_head()
    *
    *  This action is called in the admin_head action on the edit screen where your field is created.
    *  Use this action to add CSS and JavaScript to assist your render_field() action.
    *
    *  @type    action (admin_head)
    *  @since   3.6
    *  @date    23/01/13
    *
    *  @param   n/a
    *  @return  n/a
    */

    /*

    function input_admin_head() {



    }

    */




    /*
    *  input_form_data()
    *
    *  This function is called once on the 'input' page between the head and footer
    *  There are 2 situations where ACF did not load during the 'acf/input_admin_enqueue_scripts' and
    *  'acf/input_admin_head' actions because ACF did not know it was going to be used. These situations are
    *  seen on comments / user edit forms on the front end. This function will always be called, and includes
    *  $args that related to the current screen such as $args['post_id']
    *
    *  @type    function
    *  @date    6/03/2014
    *  @since   5.0.0
    *
    *  @param   $args (array)
    *  @return  n/a
    */

    /*

    function input_form_data( $args ) {



    }

    */


    /*
    *  input_admin_footer()
    *
    *  This action is called in the admin_footer action on the edit screen where your field is created.
    *  Use this action to add CSS and JavaScript to assist your render_field() action.
    *
    *  @type    action (admin_footer)
    *  @since   3.6
    *  @date    23/01/13
    *
    *  @param   n/a
    *  @return  n/a
    */

    /*

    function input_admin_footer() {



    }

    */


    /*
    *  field_group_admin_enqueue_scripts()
    *
    *  This action is called in the admin_enqueue_scripts action on the edit screen where your field is edited.
    *  Use this action to add CSS + JavaScript to assist your render_field_options() action.
    *
    *  @type    action (admin_enqueue_scripts)
    *  @since   3.6
    *  @date    23/01/13
    *
    *  @param   n/a
    *  @return  n/a
    */



    function field_group_admin_enqueue_scripts() {

        $dir = plugin_dir_url( __FILE__ );

        wp_register_script('acf-input-image-crop-options', "{$dir}js/options.js", array('jquery'), $this->settings['version']);
        wp_enqueue_script( 'acf-input-image-crop-options');

        wp_register_style('acf-input-image-crop-options', "{$dir}css/options.css");
        wp_enqueue_style( 'acf-input-image-crop-options');
    }




    /*
    *  field_group_admin_head()
    *
    *  This action is called in the admin_head action on the edit screen where your field is edited.
    *  Use this action to add CSS and JavaScript to assist your render_field_options() action.
    *
    *  @type    action (admin_head)
    *  @since   3.6
    *  @date    23/01/13
    *
    *  @param   n/a
    *  @return  n/a
    */

    /*

    function field_group_admin_head() {

    }

    */


    /*
    *  load_value()
    *
    *  This filter is applied to the $value after it is loaded from the db
    *
    *  @type    filter
    *  @since   3.6
    *  @date    23/01/13
    *
    *  @param   $value (mixed) the value found in the database
    *  @param   $post_id (mixed) the $post_id from which the value was loaded
    *  @param   $field (array) the field array holding all the field options
    *  @return  $value
    */

    /*

    function load_value( $value, $post_id, $field ) {

        return $value;

    }

    */


    /*
    *  update_value()
    *
    *  This filter is applied to the $value before it is saved in the db
    *
    *  @type    filter
    *  @since   3.6
    *  @date    23/01/13
    *
    *  @param   $value (mixed) the value found in the database
    *  @param   $post_id (mixed) the $post_id from which the value was loaded
    *  @param   $field (array) the field array holding all the field options
    *  @return  $value
    */

    /*

    function update_value( $value, $post_id, $field ) {

        return $value;

    }

    */


    /*
    *  format_value()
    *
    *  This filter is appied to the $value after it is loaded from the db and before it is returned to the template
    *
    *  @type    filter
    *  @since   3.6
    *  @date    23/01/13
    *
    *  @param   $value (mixed) the value which was loaded from the database
    *  @param   $post_id (mixed) the $post_id from which the value was loaded
    *  @param   $field (array) the field array holding all the field options
    *
    *  @return  $value (mixed) the modified value
    */



    function format_value( $value, $post_id, $field ) {

       // validate
        if( !$value )
        {
            return false;
        }
        $data = json_decode($value);
        if(!is_object($data)){
            return $value;
        }

        $value = $data->cropped_image;

        // format
        if( $field['save_format'] == 'url' )
        {
            if(is_numeric($data->cropped_image)){
                $value = wp_get_attachment_url( $data->cropped_image );
            }
            elseif(is_array($data->cropped_image)){

                $value = $this->getAbsoluteImageUrl($data->cropped_image['image']);
            }
            elseif(is_object($data->cropped_image)){
                $value = $this->getAbsoluteImageUrl($data->cropped_image->image);
            }

        }
        elseif( $field['save_format'] == 'object' )
        {
            if(is_numeric($data->cropped_image )){
                $attachment = get_post( $data->cropped_image );
                // validate
                if( !$attachment )
                {
                    return false;
                }


                // create array to hold value data
                $src = wp_get_attachment_image_src( $attachment->ID, 'full' );

                $value = array(
                    'id' => $attachment->ID,
                    'alt' => get_post_meta($attachment->ID, '_wp_attachment_image_alt', true),
                    'title' => $attachment->post_title,
                    'caption' => $attachment->post_excerpt,
                    'description' => $attachment->post_content,
                    'mime_type' => $attachment->post_mime_type,
                    'url' => $src[0],
                    'width' => $src[1],
                    'height' => $src[2],
                    'sizes' => array(),
                );


                // find all image sizes
                $image_sizes = get_intermediate_image_sizes();

                if( $image_sizes )
                {
                    foreach( $image_sizes as $image_size )
                    {
                        // find src
                        $src = wp_get_attachment_image_src( $attachment->ID, $image_size );

                        // add src
                        $value[ 'sizes' ][ $image_size ] = $src[0];
                        $value[ 'sizes' ][ $image_size . '-width' ] = $src[1];
                        $value[ 'sizes' ][ $image_size . '-height' ] = $src[2];
                    }
                    // foreach( $image_sizes as $image_size )
                }
            }
            elseif(is_array( $data->cropped_image)){
                $value = array(
                    'url' => $this->getAbsoluteImageUrl($data->cropped_image['image']),
                );
            }
            else{

                //echo 'ELSE';
            }

        }
        return $value;

    }




    /*
    *  validate_value()
    *
    *  This filter is used to perform validation on the value prior to saving.
    *  All values are validated regardless of the field's required setting. This allows you to validate and return
    *  messages to the user if the value is not correct
    *
    *  @type    filter
    *  @date    11/02/2014
    *  @since   5.0.0
    *
    *  @param   $valid (boolean) validation status based on the value and the field's required setting
    *  @param   $value (mixed) the $_POST value
    *  @param   $field (array) the field array holding all the field options
    *  @param   $input (string) the corresponding input name for $_POST value
    *  @return  $valid
    */

    /*

    function validate_value( $valid, $value, $field, $input ){

        // Basic usage
        if( $value < $field['custom_minimum_setting'] )
        {
            $valid = false;
        }


        // Advanced usage
        if( $value < $field['custom_minimum_setting'] )
        {
            $valid = __('The value is too little!','acf-image_crop'),
        }


        // return
        return $valid;

    }

    */


    /*
    *  delete_value()
    *
    *  This action is fired after a value has been deleted from the db.
    *  Please note that saving a blank value is treated as an update, not a delete
    *
    *  @type    action
    *  @date    6/03/2014
    *  @since   5.0.0
    *
    *  @param   $post_id (mixed) the $post_id from which the value was deleted
    *  @param   $key (string) the $meta_key which the value was deleted
    *  @return  n/a
    */

    /*

    function delete_value( $post_id, $key ) {



    }

    */


    /*
    *  load_field()
    *
    *  This filter is applied to the $field after it is loaded from the database
    *
    *  @type    filter
    *  @date    23/01/2013
    *  @since   3.6.0
    *
    *  @param   $field (array) the field array holding all the field options
    *  @return  $field
    */

    /*

    function load_field( $field ) {

        return $field;

    }

    */


    /*
    *  update_field()
    *
    *  This filter is applied to the $field before it is saved to the database
    *
    *  @type    filter
    *  @date    23/01/2013
    *  @since   3.6.0
    *
    *  @param   $field (array) the field array holding all the field options
    *  @return  $field
    */

    /*

    function update_field( $field ) {

        return $field;

    }

    */


    /*
    *  delete_field()
    *
    *  This action is fired after a field is deleted from the database
    *
    *  @type    action
    *  @date    11/02/2014
    *  @since   5.0.0
    *
    *  @param   $field (array) the field array holding all the field options
    *  @return  n/a
    */

    /*

    function delete_field( $field ) {



    }

    */


}


// create field
new acf_field_image_crop();

?>