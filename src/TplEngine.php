<?php

namespace xjryanse\logic;

/**
 * 模板引擎
 * 
<html>
<head>
    <title>模板引擎示例</title>
</head>
<body>
    <h1>用户列表</h1>

    {if showUsers}
        <ul>
        {foreach users as $__item__}
            <li>{$__item__.name}, {$__item__.age}岁</li>
        {/foreach}
        </ul>
    {else}
        <p>没有用户可显示。</p>
    {/if}
</body>
</html>
 * 
 */
class TplEngine {
    /**
     * 源模板字符串
     * @var type
     */
    private $tplStr;
    /**
     * 判断data
     * @var type
     */
    private $data = [];
    
    public function __construct($templateStr) {
        $this->tplStr  = $templateStr;
    }
    
    /**
     * 
     * @param type $keyOrData    key或key+数据
     * @param type $value
     */
    public function assign($keyOrData, $value = '') {
        if(!is_string($keyOrData)){
            foreach($keyOrData as $k=>$v){
                $this->data[$k] = $v;
            }
        } else {
            $key = $keyOrData;
            $this->data[$key] = $value;
        }
    }
    
    public function displayStr() {
        // Parse if statements
        $template = preg_replace_callback('/\{if\s+(.*?)\}(.*?)(\{else\}(.*?))?\{\/if\}/s', [$this, 'parseIf'], $this->tplStr);

        // Parse foreach loops
        $template = preg_replace_callback('/\{foreach\s+\$+(.*?)\s+as\s+\$+(.*?)=>\$+(.*?)\}(.*?)\{\/foreach\}/s', [$this, 'parseForeach'], $template);

        // Replace other variables
        // $template = str_replace(array_keys($this->data), $this->data, $template);
        $template = Strings::dataReplace($template, $this->data);
        
        return $template;
    }

    private function parseIf($matches) {
        $condition = trim($matches[1]);
        $ifContent = trim($matches[2]);
        $elseContent = isset($matches[3]) ? trim($matches[4]) : '';

        if (eval('return ' . $condition . ';')) {
            return $ifContent;
        } else {
            return $elseContent;
        }
    }
    
    private function parseForeach($matches) {
        $array = $this->data[trim($matches[1])];
        // 匹配到目标
        $itemTemplate = trim($matches[4]);
        $oArr = [];
        
        foreach ($array as $k=>$item) {
            $data = [];
            $data[$matches[2]] = $k;
            $data[$matches[3]] = $item;
            // $this->data['__item__'] = $item;
            $oArr[] = Strings::dataReplace($itemTemplate, $data);
        }

        return implode(PHP_EOL,$oArr);
    }
    
}
