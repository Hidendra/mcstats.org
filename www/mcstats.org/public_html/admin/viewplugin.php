<?php

define('ROOT', '../');
session_start();

require_once ROOT . '../private_html/config.php';
require_once ROOT . '../private_html/includes/database.php';
require_once ROOT . '../private_html/includes/func.php';

ensure_loggedin();

/// Is this an ajax call?
$ajax = isset($_GET['ajax']) || isset($_SERVER['HTTP_X_PJAX']) || isset($_SERVER['X-PJAX']);

/// Check for the plugin in $_GET
if (!isset($_GET['plugin'])) {
    err('No plugin provided.');
} else {
    // Load the provided plugin
    $plugin = loadPlugin(urldecode($_GET['plugin']));

    if ($plugin === null) {
        err('Invalid plugin.');
    }

    $pluginName = htmlentities($plugin->getName());
    $encodedName = urlencode($pluginName);

    $breadcrumbs = '<a href="/admin/">Administration</a> <a href="/plugin/' . $encodedName . '">Plugin: ' . $pluginName . '</a> <a href="/admin/plugin/' . $encodedName . '/view" class="current">Edit Plugin</a>';

    // If not........
    if (!$ajax) {
        send_header();
    }

    /// Can we access it?
    if (!can_admin_plugin($plugin)) {
        err('You do not have ownership access of that plugin!');
    } else {
        ?>

        <?php
        if (!$ajax) {
            echo '

                        <div class="row-fluid">
';
        }
        ?>

        <div class="col-xs-4">

            <form action="/admin/plugin/<?php echo $plugin->getName(); ?>/update" method="post" class="form-horizontal">
                <legend>
                    Basic information
                </legend>

                <div class="form-group">
                    <label class="control-label" for="name">Plugin name</label>

                    <div class="controls">
                        <input class="form-control" type="text" name="name" value="<?php echo $plugin->getName(); ?>" id="name" style="min-width: 200px" disabled/>
                    </div>
                </div>

                <div class="form-group">
                    <label class="control-label" for="authors">Authors</label>

                    <div class="controls">
                        <input class="form-control" type="text" name="authors" value="<?php echo $plugin->getAuthors(); ?>" id="authors" style="min-width: 200px"/>
                    </div>
                </div>
                <?php

                $graphs = $plugin->getAllGraphs();
                $index = 0;
                foreach ($graphs as $graph) {
                    $index++; // start at 1 as well

                    // convenient data so we aren't constantly using accessors
                    $id = $graph->getID();
                    $name = htmlentities($graph->getName());
                    $displayName = htmlentities($graph->getDisplayName());
                    $type = $graph->getType();
                    $isActive = $graph->isActive();
                    $scale = $graph->getScale();
                    $position = $graph->getPosition();
                    $disabled = $graph->isReadOnly() ? true : false;
                    echo '
                            <legend>
                                Custom graph #' . $index . '
                            </legend>

                            <!-- Register this graph -->
                            <input class="form-control" type="hidden" name="graph[' . $id . ']" value="1" style="min-width: 200px" />

                            <div class="form-group">
                                <label class="control-label" for="' . $id . '-name">Internal Name</label>

                                <div class="controls">
                                    <input class="form-control" type="text" id="' . $id . '-name" value="' . $name . '" style="min-width: 200px" disabled />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label" for="' . $id . '-displayname">Display Name</label>

                                <div class="controls">
                                    <input class="form-control" type="text" name="displayName[' . $id . ']" id="' . $id . '-displayname" style="min-width: 200px" value="' . $displayName . '"' . ($disabled ? ' disabled' : '') . ' />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label" for="' . $id . '-position">Position</label>

                                <div class="controls">
                                    <input class="form-control" type="text" name="position[' . $id . ']" id="' . $id . '-position" style="min-width: 200px" value="' . $position . '"' . ($disabled ? ' disabled' : '') . ' />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label" for="' . $id . '-type">Type</label>

                                <div class="controls">
                                    <select class="form-control" name="type[' . $id . ']" style="min-width: 200px" id="' . $id . '-type"' . ($disabled ? ' disabled' : '') . '>
                                        <option value="' . GraphType::Line . '"' . ($type == GraphType::Line ? ' selected' : '') . '>Line</option>
                                        <option value="' . GraphType::Area . '"' . ($type == GraphType::Area ? ' selected' : '') . '>Area</option>
                                        <option value="' . GraphType::Column . '"' . ($type == GraphType::Column ? ' selected' : '') . '>Column</option>
                                        <option value="' . GraphType::Stacked_Column . '"' . ($type == GraphType::Stacked_Column ? ' selected' : '') . '>Stacked Column</option>
                                        <option value="' . GraphType::Pie . '"' . ($type == GraphType::Pie ? ' selected' : '') . '>Pie</option>
                                        <option value="' . GraphType::Percentage_Area . '"' . ($type == GraphType::Percentage_Area ? ' selected' : '') . '>Percentage Area</option>
                                        <option value="' . GraphType::Donut . '"' . ($type == GraphType::Donut ? ' selected' : '') . '>Donut (req. compatible data)</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label" for="' . $id . '-active">Active</label>

                                <div class="controls">
                                    <label class="checkbox">
                                        <input type="checkbox" name="active[' . $id . ']" id="' . $id . '-active" value="1"' . ($isActive ? ' CHECKED' : '') . ($disabled ? ' disabled' : '') . '>
                                    </label>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label" for="' . $id . '-halfwidth">Half-width</label>

                                <div class="controls">
                                    <label class="checkbox">
                                        <input type="checkbox" name="halfwidth[' . $id . ']" id="' . $id . '-halfwidth" value="1"' . ($graph->isHalfwidth() ? ' CHECKED' : '') . ($disabled ? ' disabled' : '') . '>
                                    </label>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="control-label" for="' . $id . '-scale">Scale</label>

                                <div class="controls">
                                    <label class="radio inline">
                                        <input type="radio" name="scale[' . $id . ']" id="' . $id . '-scale" value="linear"' . ($scale == GraphScale::Linear ? ' CHECKED' : '') . ($disabled ? ' disabled' : '') . '> Linear
                                    </label>
                                    <label class="radio inline">
                                        <input type="radio" name="scale[' . $id . ']" value="log"' . ($scale == GraphScale::Logarithmic ? ' CHECKED' : '') . ($disabled ? ' disabled' : '') . '> Logarithmic
                                    </label>
                                </div>
                            </div>

';
                }
                ?>
                <div class="form-actions" style="padding-left: 0; text-align: center; width: 320px;">
                    <input type="submit" name="submit" value="Save changes" class="btn btn-primary"/>
                    <a href="/admin/" class="btn">Cancel</a>
                </div>

            </form>

        </div>
        <div class="clearfix"></div>

        <?php
        if (!$ajax) {
            echo '
                </div>';
        }
        ?>


    <?php
    }

}

if (!$ajax) {
    send_footer();
}