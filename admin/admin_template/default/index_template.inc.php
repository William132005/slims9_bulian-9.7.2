<!doctype html>
<html>
<head>
    <title><?php echo $page_title; ?></title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, post-check=0, pre-check=0" />
    <meta http-equiv="Expires" content="Sat, 26 Jul 1997 05:00:00 GMT" />
    <meta name="env" content="<?= isDev() ? 'dev' : 'prod' ?>"/>

    <?php
    $imagesDisk = \SLiMS\Filesystems\Storage::images();
    $icon = SWB . 'webicon.ico';
    if (isset($sysconf['webicon']) && !empty($sysconf['webicon']) && $imagesDisk->isExists($path = 'default/' . $sysconf['webicon']))
    {
        $icon = SWB . 'lib/minigalnano/createthumb.php?filename=images/' . $path . '&width=130';
    }
    ?>

    <link rel="icon" href="<?= $icon ?>" type="image/x-icon" />
    <link rel="shortcut icon" href="<?= $icon ?>" type="image/x-icon" />
    <link href="<?php echo SWB; ?>css/bootstrap.min.css?<?php echo date('this') ?>" rel="stylesheet" type="text/css" />
    <link href="<?php echo SWB; ?>css/core.css?<?php echo date('this') ?>" rel="stylesheet" type="text/css" />
    <link href="<?php echo JWB; ?>colorbox/colorbox.css?<?php echo date('this') ?>" rel="stylesheet" type="text/css" />
    <link href="<?php echo JWB; ?>chosen/chosen.css?<?php echo date('this') ?>" rel="stylesheet" type="text/css" />
    <link href="<?php echo JWB; ?>toastr/toastr.min.css?<?php echo date('this') ?>" rel="stylesheet" type="text/css" />
    <link href="<?php echo JWB; ?>jquery.imgareaselect/css/imgareaselect-default.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo JWB; ?>datepicker/css/datepicker-bs4.min.css" rel="stylesheet" />
    <link href="<?php echo $sysconf['admin_template']['css'].'?v='.date('this'); ?>" rel="stylesheet" type="text/css" />

    <script type="text/javascript" src="<?php echo JWB; ?>jquery.js"></script>
    <script type="text/javascript" src="<?php echo AWB; ?>admin_template/<?php echo $sysconf['admin_template']['theme']?>/vendor/slimscroll/jquery.slimscroll.min.js"></script>
    <script type="text/javascript" src="<?php echo JWB; ?>updater.js?v=<?php echo date('this') ?>"></script>
    <script type="text/javascript" src="<?php echo JWB; ?>gui.js??v=<?php echo date('this') ?>"></script>
    <script type="text/javascript" src="<?php echo JWB; ?>form.js?v=<?php echo date('this') ?>"></script>
    <script type="text/javascript" src="<?php echo JWB; ?>calendar.js?v=<?php echo date('this') ?>"></script>
    <script type="text/javascript" src="<?php echo JWB; ?>chosen/chosen.jquery.min.js"></script>
    <script type="text/javascript" src="<?php echo JWB; ?>chosen/ajax-chosen.min.js"></script>
    <script type="text/javascript" src="<?php echo JWB; ?>ckeditor5/ckeditor.js"></script>
    <script type="text/javascript" src="<?php echo JWB; ?>tooltipsy.js"></script>
    <script type="text/javascript" src="<?php echo JWB; ?>colorbox/jquery.colorbox-min.js"></script>
    <script type="text/javascript" src="<?php echo JWB; ?>jquery.imgareaselect/scripts/jquery.imgareaselect.pack.js"></script>
    <script type="text/javascript" src="<?php echo JWB; ?>webcam.js"></script>
    <script type="text/javascript" src="<?php echo JWB; ?>scanner.js"></script>
    <script type="text/javascript" src="<?php echo SWB; ?>js/popper.min.js"></script>
    <script type="text/javascript" src="<?php echo SWB; ?>js/bootstrap.min.js"></script>
    <script type="text/javascript" src="<?php echo JWB; ?>toastr/toastr.min.js"></script>
    <script type="text/javascript" src="<?php echo JWB; ?>datepicker/js/datepicker-full.min.js"></script>
    <?php if (file_exists(SB . 'js/datepicker/js/locales/' . substr($sysconf['default_lang'], 0,2) . '.js')): ?>
    <script type="text/javascript" src="<?php echo JWB; ?>datepicker/js/locales/<?= substr($sysconf['default_lang'], 0,2) ?>.js"></script>
    <?php endif; ?>
    <script type="text/javascript" src="<?php echo $sysconf['admin_template']['dir'].'/'.$sysconf['admin_template']['theme']; ?>/js/smooth-scrollbar.js"></script>
    <script type="text/javascript" src="<?php echo $sysconf['admin_template']['dir'].'/'.$sysconf['admin_template']['theme']; ?>/js/overscroll.js"></script>
    <?php if($sysconf['chat_system']['enabled']) : ?>
    <script src="<?php echo JWB; ?>fancywebsocket.js"></script>
    <?php endif; ?>
    <style>
        .s-user:after {
            background-color: <?= $sysconf['admin_template']['default_color']??'#000080'; ?> !important;
        }
        #sidepan {
            background-color: #ffffff !important;
            border-right: 1px solid #E2E8F0 !important;
            box-shadow: 2px 0 8px rgba(0,0,0,0.06) !important;
        }
        #sidepan .scroll-content {
            padding: 0;
        }
        /* ── Dropdown Pengaturan ── */
        #pengaturanDropdown {
            position: relative;
        }
        #pengaturanDropdown .dropdown-menu {
            position: absolute;
            top: 100%;
            left: 0;
            z-index: 9999;
            min-width: 220px;
            background: #fff;
            border: none;
            border-radius: 6px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.12);
            padding: 6px 0;
            display: none;
        }
        #pengaturanDropdown .dropdown-menu.show {
            display: block;
        }
        #pengaturanDropdown .dropdown-item {
            padding: 10px 18px;
            font-family: 'Quicksand', sans-serif;
            font-size: 14px;
            color: #333;
            transition: background 0.2s;
            white-space: nowrap;
        }
        #pengaturanDropdown .dropdown-item:hover,
        #pengaturanDropdown .dropdown-item.active {
            background-color: #f0f9ff;
            color: #00a9ff;
        }
        #pengaturanDropdown .dropdown-toggle::after {
            display: inline-block;
            width: 0;
            height: 0;
            margin-left: 5px;
            vertical-align: middle;
            border-top: 4px solid;
            border-right: 4px solid transparent;
            border-left: 4px solid transparent;
            content: "";
        }
        #pengaturanDropdown .nav-link:hover {
            color: #00a9ff;
        }
    </style>
</head>
<body>

<header id="header">
    <nav id="mainMenu">
        <?php echo $main_menu; ?>
    </nav>
</header>
    
<nav id="sidepan">
    <div class="s-user" id="profile">
        <div class="s-user-frame">
            <a href="<?php echo MWB.'system/app_user.php?changecurrent=true&action=detail'; ?>" class="s-user-photo subMenuItem">
                <?php
                if (filter_var($_SESSION['upict'], FILTER_VALIDATE_URL)) {
                    $user_image_url = $_SESSION['upict'];
                } else {
                    $user_image = $_SESSION['upict'] && file_exists(IMGBS . 'persons/' . $_SESSION['upict']) ? $_SESSION['upict'] : 'person.png';
                    $user_image_url = '../lib/minigalnano/createthumb.php?filename=' . IMG . '/persons/' . urlencode(urlencode($user_image)) . '&width=200';
                }
                ?>
                <img src="<?= $user_image_url ?>" alt="Photo <?php echo $_SESSION['realname'] ?>">
            </a>
        </div>
        <a href="<?php echo MWB.'system/app_user.php?changecurrent=true&action=detail'; ?>">
        <h4 class="s-user-name"><?php echo $_SESSION['realname']?></h4>
        <?php echo isset($_SESSION['nname']) ? $_SESSION['nname'] : __('Librarian'); ?>
        </a>
    </div>

    <?php echo $sub_menu; ?>
</nav>

<div class="loader">
    <div style="display:none;">Error</div>
    <div class="bounce1"></div>
    <div class="bounce2"></div>
    <div class="bounce3"></div>
</div>

<div id="mainContent">
    <?php echo $main_content; ?>
</div>

<div id="help">
  <a href="#" name="top" class="s-help d-none"><i class="fa fa-question-circle"></i></a>
  <a href="#" name="top" class="s-close d-none"><i class="fa fa-times"></i></a>
  <div class="s-help-content animated fade-in d-none"><!-- Place to put documentation --></div>
</div>

<footer>
    <div class="row">
        <div class="col-md-6"><?php echo SENAYAN_VERSION; ?></div>
        <div class="col-md-6 text-right"><?php echo $sysconf['page_footer']; ?></div>
    </div>
</footer>

<!-- fake submit iframe for search form, DONT REMOVE THIS! -->
<iframe name="blindSubmit" style="display: none; visibility: hidden; width: 0; height: 0;"></iframe>
<!-- <iframe name="blindSubmit" style="visibility: visible; width: 100%; height: 300px;"></iframe> -->
<!-- fake submit iframe -->

<script>
$('.loader').toggleClass('hidden').hide();

let Scrollbar = window.Scrollbar;
Scrollbar.use(window.OverscrollPlugin)
Scrollbar.init(document.querySelector('#sidepan'), {
    alwaysShowTracks: <?= $sysconf['admin_template']['always_show_tracks']??'false' ?>,
    continuousScrolling: false,
    plugins: {
      overscroll: {
        effect: 'glow'
      },
    }
});
$('.s-close').click(function(e){
    e.preventDefault();
    $('.s-help').removeClass('d-none');
    $('.s-close').addClass('d-none');
    $('.s-help-content').html('').addClass('d-none');
  });

  $('.s-help').click(function(e){
    e.preventDefault();
    if($(this).attr('href') != '#') {
      // load active style
      $('.s-help-content').addClass('d-none');
      $('.left, .right, .loader, #s-help').toggleClass('active');
      $.ajax({
        type: 'GET',
        url: $(this).attr('href')
      }).done(function( data ) {
        $('.s-help-content').html(data).removeClass('d-none');
        $('.s-close').toggleClass('d-none');
      });
    }else{
      alert('Help content will show according to available menu.')
    }
  });

  // ── DROPDOWN PENGATURAN ──────────────────────────────────────────
(function() {
    var dropdownMods = ['stock_take', 'system', 'reporting'];
    var hideCompletelyMods = ['master_file', 'serial_control'];
    var menuItems = [];

    // Ambil item yang benar-benar ada di DOM, sembunyikan dari menu asli
    $('#mainMenu a').each(function() {
        var $a = $(this);
        var href = $a.attr('href') || '';
        
        // Cek apakah modul ini harus disembunyikan sepenuhnya (hilang dari menu utama & dropdown)
        var shouldHideCompletely = false;
        for (var i = 0; i < hideCompletelyMods.length; i++) {
            if (href.indexOf('mod=' + hideCompletelyMods[i]) !== -1) {
                $a.closest('li').hide();
                shouldHideCompletely = true;
                break;
            }
        }

        if (shouldHideCompletely) return; // Lewati, jangan masukkan ke dropdown

        // Cek apakah modul ini harus dipindah ke dropdown Pengaturan
        for (var i = 0; i < dropdownMods.length; i++) {
            if (href.indexOf('mod=' + dropdownMods[i]) !== -1) {
                menuItems.push({
                    label: $.trim($a.text()),
                    url: href
                });
                $a.closest('li').hide();
                break;
            }
        }
    });

    if (menuItems.length === 0) return;

    // Bangun HTML dropdown Bootstrap
    var dropItems = menuItems.map(function(item) {
        var activeClass = (window.location.href.indexOf(item.url.split('?')[1]) !== -1) ? ' active' : '';
        return '<a class="dropdown-item' + activeClass + '" href="' + item.url + '">' + item.label + '</a>';
    }).join('');

    var dropHtml =
        '<li class="nav-item dropdown" id="pengaturanDropdown">' +
          '<a class="nav-link dropdown-toggle" href="#" id="dropPengaturan" ' +
             'data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" ' +
             'style="padding:15px;font-family:\'Quicksand\',sans-serif;cursor:pointer;">' +
            'Pengaturan' +
          '</a>' +
          '<div class="dropdown-menu" aria-labelledby="dropPengaturan">' +
            dropItems +
          '</div>' +
        '</li>';

    // Sisipkan setelah "Keanggotaan"
    var inserted = false;
    $('#mainMenu a').each(function() {
        var href = $(this).attr('href') || '';
        if (!inserted && href.indexOf('mod=membership') !== -1) {
            $(this).closest('li').after(dropHtml);
            inserted = true;
        }
    });

    // Jika tidak ditemukan anchor membership, fallback: cari teks "Keanggotaan"
    if (!inserted) {
        $('#mainMenu a').each(function() {
            if (!inserted && $.trim($(this).text()) === 'Keanggotaan') {
                $(this).closest('li').after(dropHtml);
                inserted = true;
            }
        });
    }

    // Aktifkan dropdown Bootstrap
    $('#pengaturanDropdown .dropdown-toggle').on('click', function(e) {
        e.preventDefault();
        var $menu = $(this).next('.dropdown-menu');
        $menu.toggleClass('show');
        $(this).attr('aria-expanded', $menu.hasClass('show'));
    });

    // Tutup dropdown jika klik di luar
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#pengaturanDropdown').length) {
            $('#pengaturanDropdown .dropdown-menu').removeClass('show');
            $('#pengaturanDropdown .dropdown-toggle').attr('aria-expanded', false);
        }
    });

    // Tandai aktif jika salah satu sub-item sedang aktif
    var currentHref = window.location.href;
    var isActive = menuItems.some(function(item) {
        return currentHref.indexOf(item.url.split('?')[1]) !== -1;
    });
    if (isActive) {
        $('#pengaturanDropdown > a').css('color', '#00a9ff').css('font-weight', 'bold');
    }
})();
// ── END DROPDOWN PENGATURAN ──────────────────────────────────────

$('.subMenuItem').click(function(){
    $('.s-help').removeClass('d-none');
    $('.s-close, .s-help-content').addClass('d-none');
    let get_url       = $(this).attr('href');
    let path_array    = get_url.split('/');
    let clean_path    = path_array[path_array.length-1].split('.');
    let new_pathname  = '<?php echo AWB?>help.php?url='+path_array[path_array.length-2]+'/'+clean_path[0]+'.md';
    $('.s-help').attr('href', new_pathname);
  });

// ── Sidebar Accordion ─────────────────────────────────────
$(document).off('click.smAcc').on('click.smAcc', '#sidepan .subMenuHeader', function () {
  var $header  = $(this);
  var targetId = $header.data('target');
  var $body    = $('#' + targetId);

  if (!$body.length) return;

  var isOpen = !$body.hasClass('smAcc-collapsed');
  if (isOpen) {
    $body.addClass('smAcc-collapsed');
    $header.removeClass('smAcc-open');
    $header.find('.smAcc-arrow').html('&#9654;');
  } else {
    $body.removeClass('smAcc-collapsed');
    $header.addClass('smAcc-open');
    $header.find('.smAcc-arrow').html('&#9660;');
  }
});
// ── /Sidebar Accordion ───────────────────────────────────
</script>
<?php include "chat.php" ?>
</body>
</html>
