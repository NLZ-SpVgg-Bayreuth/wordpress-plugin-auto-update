<?php
/*
Plugin Name: SpVgg Bayreuth Auto Update
Plugin URI: https://github.com/NLZ-SpVgg-Bayreuth/wordpress-plugin-auto-update
Description: This plugin allows you to update your private plugins from git repositories hosted on GitHub / GitLab / Gitea.
Version: 1.0
Author: Paul Schur
Author URI: https://github.com/pschur
License: GPL2
*/


if (isset($_GET['spvgg-auto-update']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $packages = require __DIR__.'/config.php';

    // Check package
    if (!isset($packages[$_GET['spvgg-auto-update']])) {
        http_response_code(404);
        die('Not Found');
    }

    # Check key
    if (!isset($_SERVER['X-Code']) || !password_verify($_SERVER['X-Code'], $packages[$_GET['spvgg-auto-update']]['code'])) {
        http_response_code(403);
        die('Forbidden');
    }

    # Check file
    if (!isset($_FILES['file'])) {
        http_response_code(400);
        die('Bad Request');
    }

    # Check file extension
    $file = $_FILES['file'];
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    if ($ext !== 'zip') {
        http_response_code(400);
        die('Bad Request');
    }

    # Check file type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    if ($mime !== 'application/zip') {
        http_response_code(400);
        die('Bad Request');
    }

    # Copy file in work directory
    $path = $packages[$_GET['spvgg-auto-update']]['path'];
    $filename = $path.'/'.uniqid().'.'.$ext;
    if (!move_uploaded_file($file['tmp_name'], $filename)) {
        http_response_code(500);
        die('Internal Server Error');
    }

    # Unzip file
    $zip = new ZipArchive;
    if ($zip->open($filename) === TRUE) {
        $zip->extractTo($path);
        $zip->close();
    } else {
        http_response_code(500);
        die('Internal Server Error');
    }

    # Remove zip file
    unlink($filename);
    
    # Make Backup of current plugin
    $backup = $path.'/backup-'.uniqid().'.zip';
    $zip = new ZipArchive;
    if ($zip->open($backup, ZipArchive::CREATE) === TRUE) {
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));
        foreach ($files as $file) {
            if ($file->isDir()) {
                $zip->addEmptyDir(str_replace($path.'/', '', $file.'/'));
            } else {
                $zip->addFile($file, str_replace($path.'/', '', $file));
            }
        }
        $zip->close();
    } else {
        http_response_code(500);
        die('Internal Server Error');
    }

    try {
        # Copy new files to plugin directory & replace old files
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));
        foreach ($files as $file) {
            if ($file->isFile()) {
                $new = str_replace($path.'/', '', $file);
                $old = $path.'/'.$new;
                if (file_exists($old)) {
                    unlink($old);
                }
                copy($file, $old);
            }
        }

        # Remove backup files
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));
        foreach ($files as $file) {
            if ($file->isFile() && preg_match('/backup-[a-z0-9]+\.zip/', $file)) {
                unlink($file);
            }
        }
    } catch (\Throwable $th) {
        http_response_code(500);
        die('Internal Server Error');
    }
}