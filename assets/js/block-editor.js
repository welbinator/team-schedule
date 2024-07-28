( function( blocks, element, editor ) {
    var el = element.createElement;

    blocks.registerBlockType( 'team-schedule/team-schedule-block', {
        title: 'Team Schedule',
        icon: 'calendar',
        category: 'widgets',
        edit: function() {
            return el(
                'div',
                { className: 'team-schedule-block' },
                'Team Schedule Block'
            );
        },
        save: function() {
            return el(
                'div',
                { className: 'team-schedule-block' },
                'Team Schedule Block'
            );
        }
    } );
} )( window.wp.blocks, window.wp.element, window.wp.editor );
