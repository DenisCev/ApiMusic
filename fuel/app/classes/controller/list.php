<?php 
class Controller_List extends Controller_Base
{
    public function post_create()
    {
        try
        {
            $authenticated = $this->requestAuthenticate();

            if($authenticated == true)
            {
        		if(!isset($_POST['name']))
                {
                    return $this->JSONResponse(400, 'Debes rellenar todos los campos', '');
                }

                $info = $this->getUserInfo();

                $input = $_POST;

                $listName = Model_Lists::find('all', array(
                    'where' => array(
                        array('name', $input['name']),
                        array('id_user', $info['id'])
                    ),
                ));

                if(!empty($listName))
                {
                    return $this->JSONResponse(400, 'Esa lista ya existe', '');
                }

                $list = new Model_Lists();
                $list->name = $input['name'];
                $list->editable = 1;
                $list->user = Model_Users::find($info['id']);
                $list->save();

                return $this->JSONResponse(200, 'Lista creada', '');
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

    public function post_add()
    {
        try
        {
            $authenticated = $this->requestAuthenticate();

            if($authenticated == true)
            {
                if(!isset($_POST['id_song']) || !isset($_POST['id_list']))
                {
                    return $this->JSONResponse(400, 'Debes rellenar todos los campos', '');
                }

                $info = $this->getUserInfo();

                $input = $_POST;

                $list = Model_Lists::find($input['id_list']);

                if(empty($list))
                {
                    return $this->JSONResponse(400, 'Esa lista no existe', '');
                }

                $song = Model_Songs::find($input['id_song']);

                if(empty($song))
                {
                    return $this->JSONResponse(400, 'Esa cancion no existe', '');
                }

                $addName = Model_Add::find('all', array(
                    'where' => array(
                        array('id_list', $input['id_list']),
                        array('id_song', $input['id_song'])
                    ),
                ));

                if(!empty($addName))
                {
                    return $this->JSONResponse(400, 'Esa cancion ya existe en esta lista', '');
                }

                $list = Model_Lists::find($input['id_list']);
                $list->song[] = Model_Songs::find($input['id_song']);
                $list->save();

                return $this->JSONResponse(200, 'Cancion agregada', '');
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

    public function post_removeFromList()
    {
        try
        {
            $authenticated = $this->requestAuthenticate();

            if($authenticated == true)
            {
                if(!isset($_POST['id_song']) || !isset($_POST['id_list']))
                {
                    return $this->JSONResponse(400, 'Debes rellenar todos los campos', '');
                }

                $info = $this->getUserInfo();

                $input = $_POST;

                $songsFromList = Model_Add::find('all', array(
                    'where' => array(
                        array('id_list', $input['id_list']),
                        array('id_song', $input['id_song'])
                    ),
                ));

                if(!empty($songsFromList)){
                    $idSong = 0;
                    foreach ($songsFromList as $key => $song)
                    {
                        $idSong = $song->id_song;
                        
                        $song->delete();
                    }
                    return $this->JSONResponse(200, 'Cancion eliminada de la lista', $idSong);
                }
                else
                {
                    return $this->JSONResponse(400, 'Esa cancion no existe en la lista', '');
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
    //Web
    public function get_lists()
    {
        try
        {
            $authenticated = $this->requestAuthenticate();

            if($authenticated == true)
            {
                $info = $this->getUserInfo();

                $userLists = Model_Lists::find('all', array(
                    'where' => array(
                        array('id_user', $info['id'])
                    )
                ));

                if(!empty($userLists))
                {
                    $songsOfList = array();
                    foreach ($userLists as $key => $list)
                    {
                        $songsFromList = Model_Add::find('all', array(
                            'where' => array(
                                array('id_list', $list->id)
                            ),
                        ));

                        if(!empty($songsFromList)){
                            unset($songsOfList);
                            $songsOfList = array();
                            
                            foreach ($songsFromList as $key => $RelList)
                            {
                                $songsOfList[] = Model_Songs::find($RelList->id_song);
                            }
                        }
                        else
                        {
                            unset($songsOfList);
                            $songsOfList = array();
                        }

                        $userList = array(
                            'id' => $list->id,
                            'name' => $list->name,
                            'id_user' => $list->id_user,
                            'editable' => $list->editable,
                            'songs' => $songsOfList
                        );

                        $lists[] = $userList;
                    }

                    return $this->JSONResponse(200, 'Listas obtenidas', $lists);
                }
                else
                {
                    return $this->JSONResponse(400, 'No existen listas asociadas a esta cuenta', '');
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
    //iOS
    public function get_userLists()
    {
        try
        {
            $authenticated = $this->requestAuthenticate();

            if($authenticated == true)
            {
                $info = $this->getUserInfo();

                $userLists = Model_Lists::find('all', array(
                    'where' => array(
                        array('id_user', $info['id'])
                    )
                ));

                if(!empty($userLists))
                {
                    $songsOfList = array();
                    $num = 0;
                    foreach ($userLists as $key => $list)
                    {
                        $num = $num + 1;
                        $songsFromList = Model_Add::find('all', array(
                            'where' => array(
                                array('id_list', $list->id)
                            ),
                        ));

                        if(!empty($songsFromList)){
                            unset($songsOfList);
                            $songsOfList = array();
                            
                            foreach ($songsFromList as $key => $RelList)
                            {
                                $songsOfList[] = Model_Songs::find($RelList->id_song);
                            }
                        }
                        else
                        {
                            unset($songsOfList);
                            $songsOfList = array();
                        }
                        // A causa del formato en iOS -> $num
                        $lists[$num] = array(
                            'id' => $list->id,
                            'name' => $list->name,
                            'id_user' => $list->id_user,
                            'editable' => $list->editable,
                            'songs' => $songsOfList
                        );
                    }

                    return $this->JSONResponse(200, 'Listas obtenidas', $lists);
                }
                else
                {
                    return $this->JSONResponse(400, 'No existen listas asociadas a esta cuenta', '');
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

    public function get_songs()
    {
        try
        {
            $authenticated = $this->requestAuthenticate();

            if($authenticated == true)
            {
                $info = $this->getUserInfo();

                $input = $_GET;

                $allSongs = Model_Songs::find('all');

                if(!empty($allSongs)){
                    foreach ($allSongs as $key => $song)
                    {
                        $songs[] = $song;
                    }
                    $userList = array(
                        'name' => "Todas las canciones",
                        'songs' => $songs
                    );
                    $listSongs[] = $userList;

                    return $this->JSONResponse(200, 'Canciones obtenidas', $listSongs);
                }
                else
                {
                    return $this->JSONResponse(401, 'No existen canciones', '');
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

    public function get_songsFromList()
    {
        try
        {
            if(!isset($_GET['id_list']))
            {
                return $this->JSONResponse(400, 'Debes rellenar todos los campos', '');
            }

            $authenticated = $this->requestAuthenticate();

            if($authenticated == true)
            {

                $info = $this->getUserInfo();

                $input = $_GET;

                $allSongs = Model_Add::find('all', array(
                    'where' => array(
                        array('id_list', $input['id_list'])
                    ),
                ));
                $num = 0;
                if(!empty($allSongs)){
                    foreach ($allSongs as $key => $songGroup)
                    {
                        $num = $num + 1;
                        $songs[$num] = Model_Songs::find($songGroup['id_song']);
                    }
                    
                    return $this->JSONResponse(200, 'Canciones obtenidas', $songs);
                }
                else
                {
                    return $this->JSONResponse(400, 'No existen canciones', '');
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

    public function post_edit()
    {
        try
        {
            $authenticated = $this->requestAuthenticate();

            if($authenticated == true)
            {
                if(!isset($_POST['name']) || !isset($_POST['newName']))
                {
                    return $this->JSONResponse(400, 'Debes rellenar todos los campos', '');
                }

                $info = $this->getUserInfo();
                $input = $_POST;

                $checkName = $this->validatedName($input['newName']);

                if($checkName['is'] == true)
                {
                    $userLists = Model_Lists::find('all', array(
                        'where' => array(
                            array('id_user', $info['id']),
                            array('name', $input['name']),
                        ),
                    ));

                    if(!empty($userLists))
                    {
                        foreach ($userLists as $key => $list)
                        {
                            if($list->editable == 0){
                                return $this->JSONResponse(400, 'Esta lista no se puede editar', '');
                            }
                        }

                        $nameLists = Model_Lists::find('all', array(
                            'where' => array(
                                array('id_user', $info['id']),
                                array('name', $input['newName']),
                            ),
                        ));

                        if(!empty($nameLists))
                        {
                            return $this->JSONResponse(400, 'El nombre de esa lista ya existe', '');
                        }

                        $query = DB::update('lists');
                        $query->where('name', '=', $input['name']);
                        $query->value('name', $input['newName']);
                        $query->execute();

                        return $this->JSONResponse(200, 'Nombre cambiado', '');
                    }
                    else
                    {
                        return $this->JSONResponse(400, 'Esa lista no existe', '');
                    }
                }
                else
                {
                    return $this->JSONResponse(400, $checkName['msgError'], '');
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

	public function post_delete()
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
                
                $userLists = Model_Lists::find('all', array(
                    'where' => array(
                        array('id_user', $info['id']),
                        array('id', $input['id']),
                    ),
                ));

                if(!empty($userLists))
                {
                    foreach ($userLists as $key => $list)
                    {
                        if($list->editable == 0){
                            return $this->JSONResponse(400, 'Esta lista no se puede editar', '');
                        }

                        if($list->editable == 1){
                            $list->delete();
                            return $this->JSONResponse(200, 'Lista borrada', '');
                        }
                    } 
                }
                else
                {
                    return $this->JSONResponse(400, 'Esa lista no existe', '');
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