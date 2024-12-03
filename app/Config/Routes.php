<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'LogController::index');
$routes->get('/logs', 'LogController::index');
$routes->post('/logs/addBackupLog', 'LogController::addBackupLog');
$routes->post('/logs/addReplicationLog', 'LogController::addReplicationLog');
$routes->post('/saveDbBackupLog', 'LogController::saveDBBackup');
$routes->post('/saveStorageSpace','LogController::saveStorageSpace');
$routes->get('/viewLogs', 'LogController::viewLogs'); // Route for viewing logs with an optional date filter
$routes->post('/saveAdditionalInfo', 'LogController::saveAdditionalInfo'); // Route for saving additional information4
$routes->post('/viewLogs', 'LogController::viewLogs');
$routes->get('/downloadLogsPDF', 'LogController::downloadLogsPDF');

