<?php

/**
 * Plantilla general de controladores
 * Versión 1.0.2
 *
 * Controlador de materias
 */
class materiasController extends Controller {
  function __construct()
  {    
    if (!Auth::validate()) {
      Flasher::new('Debes iniciar sesión primero.', 'danger');
      Redirect::to('login');
    }    
  }
  
  function index()
  {
    $data = 
    [
      'title' => 'Todas las materias',
      'slug' => 'materias',
      'button'   => ['url'=>'materias/agregar','text'=>'<i class="fas fa-plus"></i> Agregar materia'],
      'materias' => materiaModel::all_paginated()
    ];
    
    // Descomentar vista si requerida
    View::render('index', $data);
  }

  function ver($id)
  {
    if(!$materia=materiaModel::by_id($id)){
      Flasher::new('No existe la materia en la base de datos','danger');
      Redirect::to('materias');
    }
    $data = 
    [
      'title' => sprintf('Viendo %s',$materia['nombre']),
      'slug' => 'materias',
      'button'   => ['url'=>'materias','text'=>'<i class="fas fa-table"></i> Materias'],
      'm' => $materia
    ];
    View::render('ver',$data);
  }

  function agregar()
  {
    $data = 
    [
      'title' => 'Agregar Materia',
      'slug' => 'materias'
    ];
    View::render('agregar',$data);
  }

  function post_agregar()
  {
    try {
      if (check_posted_data(['csrf','nombre','descripcion'],$_POST) || !Csrf::validate($_POST['csrf'])){
        throw new Exception('Acceso no autorizado');
      }

      // Validar rol

      if (!is_admin(get_user_role())) {
        throw new Exception(get_notification(1));
      }

      $nombre = clean($_POST["nombre"]);
      $descripcion = clean($_POST["descripcion"]);
      //validar longitud del nombre
      if (strlen($nombre) < 5) {
        throw new Exception('El nombre es demasiado corto');
      }
      //validar que no se duplique la materia

      $sql='SELECT * FROM materias WHERE nombre = :nombre LIMIT 1';
      if (materiaModel::query($sql,['nombre' => $nombre])) {
        throw new Exception(sprintf('Ya existe la materia <b>%s</b> en la base de datos',$nombre));
      }


      $data=
      [
        'nombre'=>      $nombre,
        'Descripcion'=> $descripcion,
        'Creado'=>       now()
      ];
      //Insertar a la base de datos
      if(!$id = materiaModel::add(materiaModel::$t1,$data)){
        throw new Exception('Hubo un error al guardar el registro');
      }

      Flasher::new(sprintf('Materia <b>%s</b> agregada con exito',$nombre), 'success');
      Redirect::back();

    } catch (PDOException $e) {
      Flasher::new($e->getMessage(),'danger');
      Redirect::back();
    }catch (Exception $e) {
      Flasher::new($e->getMessage(),'danger');
      Redirect::back();
    }

  } 

  function post_editar()
  {
    try {
      if (!check_posted_data(['csrf','id','nombre','descripcion'],$_POST) || !Csrf::validate($_POST['csrf'])){
        throw new Exception('Acceso no autorizado');
      }
      $id = clean($_POST["id"]);
      $nombre = clean($_POST["nombre"]);
      $descripcion = clean($_POST["descripcion"]);

      if (!$materia = materiaModel::by_id($id)) {
        throw new Exception('No existe la materia en la base de datos');
      }

      //validar longitud del nombre
      if (strlen($nombre) < 5) {
        throw new Exception('El nombre es demasiado corto');
      }
      //validar que no se duplique la materia

      $sql='SELECT * FROM materias WHERE id != :id AND nombre = :nombre LIMIT 1';
      if (materiaModel::query($sql,['id'=>$id, 'nombre' => $nombre])) {
        throw new Exception(sprintf('Ya existe la materia <b>%s</b> en la base de datos',$nombre));
      }

      $data=
      [
        'nombre'=>      $nombre,
        'Descripcion'=> $descripcion,
        'Creado'=>       now()
      ];
      //Insertar a la base de datos
      if(!materiaModel::update(materiaModel::$t1,['id'=>$id],$data)){
        throw new Exception('Hubo un error al actualizar el registro');
      }

      Flasher::new(sprintf('Materia <b>%s</b> actualizada con exito',$nombre), 'success');
      Redirect::back();

    } catch (PDOException $e) {
      Flasher::new($e->getMessage(),'danger');
      Redirect::back();
    }catch (Exception $e) {
      Flasher::new($e->getMessage(),'danger');
      Redirect::back();
    }

  }

  function borrar($id)
  {
    // Proceso de borrado
  }
}