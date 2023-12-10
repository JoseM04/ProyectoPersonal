<?php 

class usuarioModel extends Model
{
public static function by_email($email){
$sql = 'SELECT * FROM usuario WHERE correo = :email LIMIT 1';

return($rows = parent::query($sql,['email'=> $email])) ? $rows[0] : [];
}

}

  
  