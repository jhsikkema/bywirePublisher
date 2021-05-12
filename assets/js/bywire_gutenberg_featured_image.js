function setFeaturedImageDisplay( OriginalComponent ) {
  return ( props ) => {

    // Get meta field information from the DB.
    let meta = select( 'core/editor' ).getCurrentPostAttribute( 'meta' );

    // Create featured image display option field.
    const displayOption = withState( {
       isChecked: meta.featured_image_display,
     } )( ( { isChecked, setState } ) => (
       <CheckboxControl
        label = 'Display this image at the top of the page.'
        checked={ isChecked }
        onChange={ ( isChecked ) => {
          // Update the field in the editor.
          setState( { isChecked } );

          // Save the new value to the DB.
          meta.featured_image_display = isChecked;
          dispatch( 'core/editor' ).editPost( { meta } );
        } }
      />
    ) );

    // Return the entire featured image box.
    return (
      createElement( 'div', { }, [
        // Display the original featured image box.
        createElement( OriginalComponent, props ),

        // Add a checkbox below the featured image to control display option.
        createElement( displayOption )
      ] )
    );

  }
}
