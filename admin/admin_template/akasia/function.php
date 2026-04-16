<?php
/**
* Custom Menu Layout
*
* Copyright (C) 2015 Eddy Subratha (eddy.subratha@gmail.com)
*
* This program is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program; if not, write to the Free Software
* Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
*
*/


// be sure that this file not accessed directly
if (!defined('INDEX_AUTH')) {
    die("can not access this file directly");
} elseif (INDEX_AUTH != 1) {
    die("can not access this file directly");
}

include_once '../sysconfig.inc.php';

// Generate Menu
function main_menu()
{
  global $dbs;
  $modules_dir 	  = 'modules';
  $module_table   = 'mst_module';
  $module_list 	  = array();
  $_menu 	        = '';
  $icon           = array(
    'home'           => 'fa fa-home',
    'bibliography'   => 'fa fa-bookmark',
    'circulation'    => 'fa fa-clock-o',
    'membership'     => 'fa fa-user',
    'master_file'    => 'fa fa-pencil',
    'stock_take'     => 'fa fa-suitcase',
    'system'         => 'fa fa-keyboard-o',
    'reporting'      => 'fa fa-file-text-o',
    'serial_control' => 'fa fa-barcode',
    'logout'         => 'fa fa-close',
    'opac'           => 'fa fa-desktop'
    );
  //fake, just for translation po... xieyh :(
  $translations          = array(
    'bibliography'   => __('Bibliography'),
    'master_file'    => __('Master File'),
  );
  $appended_first  = '<li><input type="radio" name="s-menu" id="home" role="button"><label for="home" class="menu home"><i class="nav-icon '.$icon['home'].'"></i> <span class="s-menu-title">'.__('Shortcut').'</span></label><input type="radio" name="s-menu" class="s-menu-close" id="home-close" role="button"><label for="home-close" class="menu home s-current s-menu-hide"><i class="nav-icon '.$icon['home'].'"></i> <span class="s-menu-title">'.__('Shortcut').'</span></label>';
  $_mods_q = $dbs->query('SELECT * FROM '.$module_table);
  while ($_mods_d = $_mods_q->fetch_assoc()) {
    $module_list[] = array('name' => $_mods_d['module_name'], 'path' => $_mods_d['module_path'], 'desc' => $_mods_d['module_desc']);
  }
  $_menu 	.= '<ul class="nav">';
  $_menu 	.= $appended_first;
  $_menu 	.= @sub_menu('default', $module_list);
  $_menu 	.= '</li>'."\n";
  $_menu 	.= '<li><a class="menu dashboard" href="'.AWB.'index.php"><i class="nav-icon fa fa-dashboard"></i> <span class="s-menu-title">'.__('Dashboard').'</span></a></li>';
  $_menu 	.= '<li><a class="menu opac" href="'.SWB.'index.php" target="_blank"><i class="nav-icon '.$icon['opac'].'"></i> <span class="s-menu-title">' . __('OPAC') . '</span></a></li>';
  if ($module_list) {
    foreach ($module_list as $_module) {
      $_formated_module_name = ucwords(str_replace('_', ' ', $_module['name']));
      $_mod_dir = $_module['path'];
      if (isset($_SESSION['priv'][$_module['path']]['r']) && $_SESSION['priv'][$_module['path']]['r'] && file_exists($modules_dir.DS.$_mod_dir)) {
        $_icon = isset($icon[$_module['name']])?$icon[$_module['name']]:'fa fa-bars';
        $_menu .= '<li><input type="radio" name="s-menu" id="'.$_module['name'].'" role="button"><label for="'.$_module['name'].'" class="menu '.$_module['name'].'" title="'.$_module['desc'].'"><i class="nav-icon '.$_icon.'"></i> <span class="s-menu-title">'.__($_formated_module_name).'</span></label><input type="radio" name="s-menu" class="s-menu-close" id="'.$_module['name'].'-close" role="button"><label for="'.$_module['name'].'-close" class="menu '.$_module['name'].' s-current s-menu-hide"><i class="nav-icon '.$_icon.'"></i> <span class="s-menu-title">'.__($_formated_module_name).'</span></label>';
        $_menu .= sub_menu($_mod_dir, $_module);
        $_menu .= '</li>';
      }
    }
  }
  $_menu .= '<li><a class="menu logout" href="logout.php"><i class="nav-icon '.$icon['logout'].'"></i> <span class="s-menu-title">' . __('Logout') . '</span></a></li>';
  $_menu .= '</ul>';
  echo $_menu;
}

function sub_menu($str_module = '', $_module = array())
{
    global $dbs;
    $modules_dir 	= 'modules';
    $_submenu 		= '<div id="sidepan"><ul class="nav">';
    $_submenu_file 	= $modules_dir.DS.$_module['path'].DS.'submenu.php';

    $plugin_menus = \SLiMS\Plugins::getInstance()->getMenus($str_module);

    if (file_exists($_submenu_file)) {
        include $_submenu_file;
        $menu = array_merge($menu ?? [], $plugin_menus);

        if ($_SESSION['uid'] > 1) {
            $tmp_menu = [];
            if (isset($menu) && count($menu) > 0) {
                foreach ($menu as $item) {
                  if (in_array(md5($item[1]), $_SESSION['priv'][$str_module]['menus']) || $item[0] == 'Header' ) $tmp_menu[] = $item;
                }
            }
            $menu = $tmp_menu;
        }
    } else {
  include 'default/submenu.php';
	$shortcuts = get_shortcuts_menu();
	foreach ($shortcuts as $shortcut) {
	  $path = preg_replace('@^.+?\|/@i', '', $shortcut);
	  $label = preg_replace('@\|.+$@i', '', $shortcut);
	  $menu[] = array(__($label), MWB.$path, __($label));
	}
    }

    // Build accordion groups
    $groups    = [];
    $cur_group = null;

    foreach ($menu as $i => $_list) {
        if ($_list[0] == 'Header') {
            if ($cur_group !== null) {
                $groups[] = $cur_group;
            }
            $cur_group = ['label' => $_list[1], 'items' => []];
        } else {
            if ($cur_group === null) {
                $cur_group = ['label' => '', 'items' => []];
            }
            $cur_group['items'][] = ['index' => $i, 'data' => $_list];
        }
    }
    if ($cur_group !== null) {
        $groups[] = $cur_group;
    }

    if (empty($groups)) {
        $_submenu .= '</ul></div>';
        return $_submenu;
    }

    // Detect active group by matching current URL
    $current_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
        . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    $active_group_idx = 0;
    foreach ($groups as $gi => $grp) {
        foreach ($grp['items'] as $item) {
            if (!empty($item['data'][1]) && strpos($current_url, $item['data'][1]) !== false) {
                $active_group_idx = $gi;
                break 2;
            }
        }
    }

    $pfx = 'acc-' . preg_replace('/[^a-z0-9]/', '', strtolower($str_module));

    foreach ($groups as $gi => $grp) {
        $group_id   = $pfx . '-g' . $gi;
        $is_open    = ($gi === $active_group_idx);
        $open_class = $is_open ? ' s-acc-open' : '';

        if (!empty($grp['label'])) {
            $_submenu .= '<li class="s-acc-header' . $open_class . '" data-target="' . $group_id . '">'
                . '<span class="s-acc-label">' . $grp['label'] . '</span>'
                . '<span class="s-acc-arrow">' . ($is_open ? '&#9660;' : '&#9654;') . '</span>'
                . '</li>' . "\n";
        }

        $_submenu .= '<li class="s-acc-body' . ($is_open ? '' : ' s-acc-collapsed') . '" id="' . $group_id . '"><ul class="nav s-acc-inner">' . "\n";
        foreach ($grp['items'] as $item) {
            $i      = $item['index'];
            $_list  = $item['data'];
            $_submenu .= '<li><a class="menu s-current-child submenu-' . $i . ' ' . strtolower(str_replace(' ', '-', $_list[0])) . '" href="' . $_list[1] . '" title="' . (isset($_list[2]) ? $_list[2] : $_list[0]) . '"><i class="nav-icon fa fa-bars"></i> ' . $_list[0] . '</a></li>' . "\n";
        }
        $_submenu .= '</ul></li>' . "\n";
    }

    $_submenu .= '</ul></div>';
    return $_submenu;
}

function get_shortcuts_menu()
{
    global $dbs;
    $shortcuts = array();
    $shortcuts_q = $dbs->query('SELECT * FROM setting WHERE setting_name LIKE \'shortcuts_'.$dbs->escape_string($_SESSION['uid']).'\'');
    $shortcuts_d = $shortcuts_q->fetch_assoc();
    if ($shortcuts_q->num_rows > 0) {
      $shortcuts = unserialize($shortcuts_d['setting_value']);
    }
    return $shortcuts;
}
