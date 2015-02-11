<?php

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_configcheckbox('magtest/showmymoodle',
                                                    get_string('configshowmymoodle', 'mod_magtest'),
                                                    get_string('configshowmymoodledesc', 'mod_magtest'), 1));
}