<?php
// Create custom roles on plugin activation
function avp_add_custom_roles() {
    add_role('avp_user', 'AVP User', [
        'read' => true,
    ]);

    add_role('avp_sponsor', 'AVP Sponsor', [
        'read' => true,
    ]);
}
register_activation_hook(__FILE__, 'avp_add_custom_roles');

// Remove roles on deactivation (اختیاری)
function avp_remove_custom_roles() {
    remove_role('avp_user');
    remove_role('avp_sponsor');
}
register_deactivation_hook(__FILE__, 'avp_remove_custom_roles');
