<?php
namespace xjryanse\logic;

use xjryanse\logic\DbOperate;
use xjryanse\logic\File;
use think\Container;
use think\Db;
use Exception;
/**
 * 生成文件
 */
class Runtime
{
    /**
     * 生成模型和服务层类
     * @param type $tableName   表名
     * @param type $modelDesc   描述
     * @param type $prefix      支持前缀
     */
    public static function generate( )
    {
        $tableName = 'w_user';
        $columns = DbOperate::columns($tableName);
        
        $content = '<?php ' . PHP_EOL . 'return ';
        $content .= var_export($columns, true) . ';';

        file_put_contents(Container::get('app')->getRuntimePath() . 'xjryanse' . DIRECTORY_SEPARATOR .  $tableName . '.php', $content);
    }
    /**
     * 表字段，缓存文件名
     */
    public static function tableColumnFileName($tableName){
        return Container::get('app')->getRuntimePath() . 'xjryanse' . DIRECTORY_SEPARATOR . 'db_column' . DIRECTORY_SEPARATOR .  $tableName . '.php';
    }
    /**
     * 生成表字段
     */
    public static function tableColumnsGenerate(){
        $tables = Db::query('show tables');
        foreach($tables as $v){
            $t          = array_values($v);
            $tableName  = $t[0];
            // 数据内容
            $columns    = DbOperate::columns($tableName);
            // 保存路径
            $filePath = self::tableColumnFileName($tableName);
            self::dataToFile($columns, $filePath);
        }
    }
    /**
     * 缓存数据写入文件
     * @param type $data
     * @param type $filePath
     * @return type
     * @throws Exception
     */
    public static function dataToFile($data,$filePath){
        $content    = '<?php ' . PHP_EOL . 'return ';
        $content    .= var_export($data, true) . ';';

        File::unlink($filePath);
        if (!file_exists(dirname($filePath)) && !mkdir(dirname($filePath), 0777, true)) {
            throw new Exception('创建目录'. dirname($filePath) .'失败');
        }
        // 写入缓存文件
        return file_put_contents($filePath, $content);            
    }
    /**
     * 数据表全量缓存（一般为系统表/配置表）
     * @param type $tableName
     * @return type
     */
    public static function tableFullCacheFileName($tableName){
        return Container::get('app')->getRuntimePath() . 'xjryanse' . DIRECTORY_SEPARATOR . 'tb_data' . DIRECTORY_SEPARATOR .  $tableName . '.php';
    }

    /**
     * 数据表全量缓存
     */
    public static function tableFullCache(){
        $tables[] = 'w_system_column';
        $tables[] = 'w_system_column_list';
        $tables[] = 'w_system_ability';
        $tables[] = 'w_system_area';
        $tables[] = 'w_system_cate';
        $tables[] = 'w_system_company';
        $tables[] = 'w_universal_page';
        $tables[] = 'w_universal_page_item';
        $tables[] = 'w_universal_item_form';
        $tables[] = 'w_universal_item_table';
        $tables[] = 'w_universal_item_btn';
        $tables[] = 'w_universal_item_grid';
        $tables[] = 'w_universal_item_menu';
        $tables[] = 'w_universal_company_default_page';
        $tables[] = 'w_universal_group';
        $tables[] = 'w_wechat_we_pub_template_msg';
        $tables[] = 'w_generate_template';
        $tables[] = 'w_generate_template_field';
        $tables[] = 'w_user_auth_access';
        $tables[] = 'w_wechat_we_app';
        $tables[] = 'w_wechat_we_pub';
        $tables[] = 'w_wechat_wx_pay_config';
        
        foreach($tables as $t){
            $con = [];
            if(DbOperate::hasField($t,'status')){
                $con[] = ['status','=',1];
            }
            $service = DbOperate::getService($t);
            if(!$service){
                continue;
            }
            // 数据内容
            // $dataAll    = Db::table($t)->where($con)->select();
            $dataAll    = $service::selectX($con);
            // 保存路径
            $filePath   = self::tableFullCacheFileName($t);
            self::dataToFile($dataAll, $filePath);
        }
    }
    
    
}
