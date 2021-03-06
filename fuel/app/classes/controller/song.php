<?php 
class Controller_Song extends Controller_Base
{
    public function post_create()
    {
        try
        {
            $authenticated = $this->requestAuthenticate();

            if($authenticated == true)
            {
                $info = $this->getUserInfo();
                $user = Model_Users::find($info['id']);
            }
            else
            {
                return $this->JSONResponse(400, 'Error de autenticación', '');
            }

            if($user->rol['type'] == 'admin')
            {
            	if(!isset($_POST['name']) || 
                    !isset($_POST['artist']) || 
                    !isset($_POST['urlSong']) ||
                    !isset($_POST['reproductions']))
                {
                    return $this->JSONResponse(400, 'Debes rellenar todos los campos', '');
                }

                $input = $_POST;

                $song = new Model_Songs();
                $song->name = $input['name'];
                $song->artist = $input['artist'];
                $song->urlSong = $input['urlSong'];
                $song->reproductions = $input['reproductions'];

                $song->save();

                $lists = Model_Lists::find('all', array(
                    'where' => array(
                        array('name', 'Todas las canciones')
                    )
                ));
                if(!empty($lists)){
                    foreach ($lists as $key => $list)
                    {
                        $addName = Model_Add::find('all', array(
                        'where' => array(
                            array('id_list', $list->id),
                            array('id_song', $song->id)
                            ),
                        ));

                        if(!empty($addName))
                        {
                            return $this->JSONResponse(400, 'Esa cancion ya existe en esta lista', '');
                        }

                        $list->song[] = Model_Songs::find($song->id);
                        $list->save();
                    }
                }
                return $this->JSONResponse(200, 'Cancion creada', '');
            }
            else
            {
            	return $this->JSONResponse(400, 'Error de autenticación', '');
            }
        }
        catch (Exception $e)
        {
            return $this->JSONResponse(500, 'Error del servidor : $e', '');
        }
    }

	public function post_delete()
	{
        try
        {
            $authenticated = self::requestAuthenticate();

            if($authenticated == true)
            {
                $info = $this->getUserInfo();
                $user = Model_Users::find($info['id']);
            }
            else
            {
                return $this->JSONResponse(400, 'Error de autenticación', '');
            }

            if($user->rol['type'] == 'admin')
            {
                $input = $_POST;
                if(array_key_exists('id', $input))
                {
                    $song = Model_Songs::find($input['id']);
                    if(!empty($song))
                    {

                        $song->delete();
                		return $this->JSONResponse(200, 'Cancion borrada', '');
                    }
                    else
                    {
                		return $this->JSONResponse(400, 'Esa cancion no existe', '');
                    }
                }
                else
                {	
                    return $this->JSONResponse(400, 'Debes rellenar todos los campos', '');
                }  	
            }
            else
            {
                return $this->JSONResponse(400, 'Error de autenticación', '');
            }
        }
        catch (Exception $e)
        {
            return $this->JSONResponse(500, 'Error del servidor : $e', '');
        }
	}

    public function post_upReproductions()
    {
       try
        {
            $authenticated = $this->requestAuthenticate();

            if($authenticated == true)
            {
                if(!isset($_POST['id'])) 
                {
                    return $this->JSONResponse(400, 'Debes rellenar todos los campos', '');
                }   

                $info = $this->getUserInfo();

                $input = $_POST;

                $song = Model_Songs::find($input['id']);

                $actualRep = $song->reproductions;

                $newReproductionNumber = $actualRep + 1;
                
                $query = DB::update('songs');
                $query->where('id', '=', $input['id']);
                $query->value('reproductions', $newReproductionNumber);
                $query->execute();

                return $this->JSONResponse(200, 'reproductions updated', '');
            }
            else
            {
                return $this->JSONResponse(400, 'Error de autenticación', '');
            }
        }
        catch (Exception $e)
        {
            return $this->JSONResponse(500, 'Error del servidor : $e', '');
        }
    }

	public function post_edit()
    {
        try
        {
            $authenticated = $this->requestAuthenticate();

            if($authenticated == true)
            {
                $info = $this->getUserInfo();
                $user = Model_Users::find($info['id']);
            }
            else
            {
                return $this->JSONResponse(400, 'Error de autenticación', '');
            }

            if($user->rol['type'] == 'admin')
            {
            	if(!isset($_POST['id']))
                {
                    return $this->JSONResponse(400, 'Debes rellenar todos los campos', '');
                }

                $input = $_POST;

                $song = Model_Songs::find($info['id']);

                if(!empty($song))
                {

	                if(array_key_exists('name', $input))
	                {
	                	$query = DB::update('songs');
	                    $query->where('id', '=', $input['id']);
	                    $query->value('name', $input['name']);
	                    $query->execute();
	                    $query = null;
	                }

	                if(array_key_exists('artist', $input))
	                {
	                	$query = DB::update('songs');
	                    $query->where('id', '=', $input['id']);
	                    $query->value('artist', $input['artist']);
	                    $query->execute();
	                    $query = null;
	                }

	                if(array_key_exists('urlSong', $input))
	                {
	                	$query = DB::update('songs');
	                    $query->where('id', '=', $input['id']);
	                    $query->value('urlSong', $input['urlSong']);
	                    $query->execute();
	                    $query = null;
	                }
                	return $this->JSONResponse(200, 'Operacion realizada con exito', '');
                }
                else
                {
                	return $this->JSONResponse(400, 'Esa cancion no existe', '');
                }
            }
            else
            {
                return $this->JSONResponse(400, 'Error de autenticación', '');
            }
        }
        catch (Exception $e)
        {
            return $this->JSONResponse(500, 'Error del servidor : $e', '');
        }
    }
}