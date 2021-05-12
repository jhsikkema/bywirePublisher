
function MyPlugin({}) {
    return el(
        PluginPostStatusInfo,
        {
            className: 'my-plugin'
        },
        el(
            CheckboxControl,
            {
                name: 'notify',
                label: __( 'Send notification' ),
            }
        )
    );
}

registerPlugin( 'my-plugin', {
    render: MyPlugin
} );
