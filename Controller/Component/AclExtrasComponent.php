<?php
/**
 * AclExtrasComponent Class
 *
 * Saves permissions in Session   
 * Inspired by http://www.mainelydesign.com/blog/view/getting-all-acl-permissions-in-one-lookup-cakephp-1-3
 **/
class AclExtrasComponent extends Component {
    
    var $components = array('Acl', 'Auth', 'Session');
    var $options = array('model'=>'Aco', 'field'=>'alias');
    
    //used for recursive variable setting/checking
    var $perms = array();   //for ACL defined permissions
    var $permissionsArray = array();    //for all permissions
    var $inheritPermission = array();   //array indexed by level to hold the inherited permission

    public function create($controller, $options = array()) {
        $this->options = array_merge($this->options, $options);
        
        //GET ACL PERMISSIONS
        $group_id = $controller->Auth->user('user_group_id');
        $acos = $controller->Acl->Aco->find('threaded');
        $group_aro = $controller->Acl->Aro->find('threaded',array('conditions'=>array('Aro.foreign_key'=>$group_id, 'Aro.model'=>'UserGroup')));
        $group_perms = Set::extract('{n}.Aco', $group_aro);
        $pAco = array();
        foreach($group_perms[0] as $value) {
            $pAco[$value['id']] = $value;
        }
        $user_id = $controller->Auth->user('id');
        $user_aro = $controller->Acl->Aro->find('threaded',array('conditions'=>array('Aro.foreign_key'=>$user_id, 'Aro.model'=>'User')));
        $user_perms = Set::extract('{n}.Aco', $user_aro);
        foreach($user_perms[0] as $value) {
            $pAco[$value['id']] = $value;
        }
        
        $this->perms = $pAco;
        $this->_addPermissions($acos, $this->options['model'], $this->options['field'], 0, '');
        
        $controller->Session->write('Auth.Permissions', $this->permissionsArray);
        return $this->permissionsArray;
    }
    
    private function _addPermissions($acos, $modelName, $fieldName, $level, $alias) {
 
        foreach ($acos as $key=>$val)
        {
            $thisAlias = $alias . $val[$modelName][$fieldName];
             
            if(isset($this->perms[$val[$modelName]['id']])) {
                $curr_perm = $this->perms[$val[$modelName]['id']];
                if($curr_perm['Permission']['_create'] == 1) {
                    $this->permissionsArray[] = $thisAlias;
                    $this->inheritPermission[$level] = 1;
                } else {
                    $this->inheritPermission[$level] = -1;
                }
            } else {
                if(!empty($this->inheritPermission)) {
                    //check for inheritedPermissions, by checking closest array element
                    $revPerms = array_reverse($this->inheritPermission);             
                    if($revPerms[0] == 1) {
                        $this->permissionsArray[] = $thisAlias; //the level above was set to 1, so this should be a 1
                    }
                     
                }
            }
 
            if(isset($val['children'][0])) {
                $old_alias = $alias;
                $alias .= $val[$modelName][$fieldName] .'/';
                $this->_addPermissions($val['children'], $modelName, $fieldName, $level+1, $alias);
                unset($this->inheritPermission[$level+1]);  //don't want the last level's inheritance, in case it was set
                unset($this->inheritPermission[$level]);    //don't want this inheritance anymore, in case it was set
                $alias = $old_alias;
            }
        }
 
        return;
    }
}
