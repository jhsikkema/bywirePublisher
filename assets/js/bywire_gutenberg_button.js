var el = wp.element.createElement;
var __ = wp.i18n.__;
var registerPlugin = wp.plugins.registerPlugin;
var PluginPostStatusInfo = wp.editPost.PluginPostStatusInfo;
var PluginDocumentSettingPanel   = wp.editPost.PluginDocumentSettingPanel
var Checkbox = wp.components.CheckboxControl;
var FormToggle = wp.components.FormToggle;
//var WithState  = wp.compose
var withSelect   = wp.data.withSelect
var withDispatch = wp.data.withDispatch;

var mapSelectToProps = function( select ) {
    return {
        metaFieldValue: select( 'core/editor' )
            .getEditedPostAttribute( 'meta' )
        [ 'publish_to_bywire' ],
        metaFieldValue2: select( 'core/editor' )
            .getEditedPostAttribute( 'meta' )
        [ 'share_images_to_bywire' ]

    }
}


 
 
var mapDispatchToProps = function( dispatch ) {
    return {
        setMetaFieldValue: function( value ) {
            dispatch( 'core/editor' ).editPost(
		{ meta: { publish_to_bywire: value } }
            );
        },
        setMetaFieldValue2: function( value ) {
            dispatch( 'core/editor' ).editPost(
		{ meta: { share_images_to_bywire: value } }
            );
        }
    }
}

function set_publish(content, action) {
    var url = bywire_data.plugin_dir+"class.bywire.php"

    var self = this
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            try {
                var response = JSON.parse(this.responseText);
            } catch (err) {
                alert(this.responseText)
                return;
	    }
	}
    }
    var params = "action="+action+"&value="+content;
    xmlhttp.open("POST",  url, true);
    xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xmlhttp.send(params);
}

function BywireEditorSettingPlugin(props) {
    return el(
        PluginDocumentSettingPanel,
        {
            className: 'bywire-editor-setting-plugin',
            title: 'Bywire',
	    icon:  'admin-site-alt3'
        },
        //__( 'Bywire Setting Panel' ),
        el(
            Checkbox,
            {
                name: 'publish_to_bywire',
                id:   'publish_to_bywire',
                label: __( 'Post to Bywire' ),
		checked: props.metaFieldValue,
		onChange: function( content ) {
                    props.setMetaFieldValue( content );
		    set_publish(content, "set-publish");
		},
                help: __( 'Submits your posts to the Bywire network' ),
            }),
	el(
            Checkbox,
            {
                name: 'share_images_to_bywire',
                id:   'share_images_to_bywire',
                label: __( 'Share images with Bywire' ),
		checked: props.metaFieldValue2,
		onChange: function( content ) {
                    props.setMetaFieldValue2( content );
		    set_publish(content, "set-share-images");
		},
                help: __( 'I own the copyrights and share the images of this post with Bywire' ),
            }
        )
    );
}

var BywireEditorSettingPluginWithData = withSelect(mapSelectToProps)(BywireEditorSettingPlugin)
 var BywireEditorSettingPluginWithDataAndActions = withDispatch( mapDispatchToProps )( BywireEditorSettingPluginWithData );
 
registerPlugin( 'bywire-editor-setting-plugin', {
    render: BywireEditorSettingPluginWithDataAndActions,
} );

function check_published(postID) {
    var url = bywire_data.plugin_dir+"class.bywire.php"

        var self = this
        var xmlhttp = new XMLHttpRequest();
        xmlhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                try {
                    var response = JSON.parse(this.responseText);
                } catch (err) {
                    alert(this.responseText)
                    return;
		}
		if (response["has_data"]) {
		    if(response["success"]) {
			swal("Post Published", response["message"], "success");
		    }else{
			swal(response["error_code"], response["message"], "error");
		    }
		}
	    }
	}
	var params = "action=check-publish";
    xmlhttp.open("POST",  url, true);
    xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xmlhttp.send(params);
}



wp.data.subscribe(function () {
    var editor           = wp.data.select('core/editor')
    var isSavingPost     = wp.data.select('core/editor').isSavingPost();
    var isAutosavingPost = wp.data.select('core/editor').isAutosavingPost();
    var isPublishingPost = wp.data.select('core/editor').isPublishingPost();
    var postID           = wp.data.select('core/editor').getCurrentPostId();
    var setFeaturedImage = editor.getEditedPostAttribute('featured_media');
    //# isCurrentPostPublished
    //# isPublishingPost
    if (isPublishingPost || (isSavingPost && !isAutosavingPost)) {
	check_published(postID);
    }
    if (setFeaturedImage) {
	const featuredImageId = wp.data.select( 'core/editor' ).getEditedPostAttribute( 'featured_media' );

	set_publish(featuredImageId, "set-featured-image")
	//wp.data.dispatch( 'core/editor' ).autosave( );
    }
})


