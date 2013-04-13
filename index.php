<?php
require 'defines.php';
require 'functions.php';
require 'person.php';
require 'SimpleImage.php';

if (!checkSetup()) {
    doInstall();
    header("Location: index.php?view=card&step=first");
    exit(0);
}

if (!isset($_GET['view'])){
    header("Location: index.php?view=card&step=first");
    exit(0);
}

if (!file_exists("config.json")) {
    saveEmptyConfig();
}

if ($_GET['view'] == "delete") {
    wipeContents();

} elseif ($_GET['view'] == "zip") {
    outputZipFile();

} else if ($_GET['view'] == "card") {
    showIDCardEditor(getDynamicFields(), getDefaultValues());
}
