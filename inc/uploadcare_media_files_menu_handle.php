<?php
    global $wp_version;

    list($wp_ver_main, $wp_ver_major) = explode('.', $wp_version);

    $api = uploadcare_api();

    wp_enqueue_script('uploadcare-main');
    wp_enqueue_style('media');

    $type = 'uploadcare_files';

    $page = 1;
    if (isset($_GET['page_num'])) {
        $page = $_GET['page_num'];
    }

    function change_param($param, $value) {
        $uri = str_replace('%7E', '~', $_SERVER['REQUEST_URI']);
        $parsed = parse_url($uri);
        $path = $parsed['path'];
        $query = array();
        parse_str($parsed['query'], $query);
        $query[$param] = $value;
        return $path . '?' . http_build_query($query);
    }

    function get_total_pages($api) {
        $p_info = $api->getFilePaginationInfo(1);
        return $p_info['pages'];
    }

    function get_file_list_and_pages($api, $page = 1) {
        # modified version of $api->getFileList()

        $limit = 25;
        $data = $api->__preparedRequest('file_list', 'GET', array('page' => $page, 'limit' => $limit));

        $result = array();
        foreach ((array)$data->results as $file_raw) {
          $result[] = new Uploadcare\File($file_raw->uuid, $api, $file_raw);
        }
        return array($result, $data->pages);
    }

    list($files, $pages) = get_file_list_and_pages($api, $page);

    function paginator($pages, $page) {
        if ($pages > 1) { ?>
    <div>
    Pages:
    <?php for ($i = 1; $i <= $pages; $i++): ?>
        <?php if ($i == $page): ?>
            <span style="margin-left: 5px;"><?php echo $i; ?></span>
        <?php else: ?>
            <a href="<?php echo change_param('page_num', $i); ?>" style="margin-left: 5px;"><?php echo $i; ?></a>
        <?php endif; ?>
    <?php endfor; ?>
    </div>
    <?php
        }
    }

    if ($wp_ver_main == 3 and $wp_ver_major < 5 ) {
        echo media_upload_header();
    }

    echo $api->widget->getScriptTag();
    ?>

    <script type="text/javascript">
      var win = window.dialogArguments || opener || parent || top;
    </script>

    <div id="uploadcare-lib-container">
    <?php paginator($pages, $page); ?>
    <div style="padding-top: 20px; margin-left: 10px;">
        <div>
            <?php foreach ($files as $file): ?>
                <div style="float: left; width: 110px; height: 110px; margin-left: 10px; margin-bottom: 10px; text-align: center;">
                    <a href="javascript: win.ucEditFile('<?php echo $file->getFileId() ?>');">
                        <?php if ($file->is_file): ?>
                            <div style="width: 110px; height: 100px;line-height: 100px;">
                                <img src="https://ucarecdn.com/assets/images/logo.png" />
                            </div>
                            <br />
                            <?php echo $file->filename ?>
                        <?php else: ?>
                            <img src="<?php echo $file->scaleCrop(100, 100, true); ?>" />
                        <?php endif; ?>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
        <br class="clear">
    </div>
    <?php paginator($pages, $page); ?>
    </div>
    <div id="uploadcare-panel-container"></div>
    <div id="uploadcare-more-container" style="text-align: center;">
        <a href="javascript:;" class="browser button button-hero" id="uploadcare-more">Upload more</a>
    </div>
