<?php
/**
 * 数据验证类
 * @author: wjr<wangjr129@163.com>
 * @Date: 2017/12/12 14:15
 */
namespace Lethe\Lib;

class Validate
{
    /**
     * 要验证的数据
     * @var array
     */
    public $data = [];

    /**
     * 自定义规则
     *
     * @var array
     */
    public static $rules = [];

    /**
     * 数据绑定的验证规则
     * @var array
     */
    public $dateBindRule = [];

    /**
     * 错误提示
     *
     * @var array
     */
    public static $errorTip = [
        'require' => "不能为空",
        'string' => "必须为字符串",
        'gt' => "必须大于 %s",
        'lt' => "必须小于 %s",
        'lengthGt' => "长度必须大于 %d",
        'lengthLt' => "长度必须小于 %d",
        'in' => "无效的值",
        'notIn' => "无效的值",
        'length' => "字符串长度必须在 %d, %d之间",
        'empty' => "必须为空",
        'arr' => "必须是数组",
        'email' => "无效邮箱地址",
        'ip' => "无效IP地址",
        'number' => "只能是数字",
        'int' => "只能是整数",
        'bool' => "只能是布尔值",
        'card' => "必须是身份证",
        'mobile' => "手机号码不正确",
        'phone' => "固话格式不正确",
        'url' => "无效的URL",
        'zip' => "邮政编码不对",
        'qq' => "qq号格式不正确",
        'english' => "只能包括英文字母(A-za-z)",
        'chinese' => "只能为中文"
    ];

    /**
     * 验证后的错误信息
     *
     * @var array
     */
    public $errorMsg = [];

    /**
     * 字段别名
     *
     * @var array
     */
    public $label = [];

    /**
     * 包含要验证数据的数组
     *
     * @param array $data 包含要验证数据的数组
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * 添加一个自定义的验证规则
     * @param  string $name
     * @param  mixed $callback
     * @param  string $message
     * @throws Exception
     */
    public static function addRule($name, $callback, $message = 'error param')
    {
        if (!is_callable($callback)) {
            throw new Exception("param {$callback} must can callable");
        }
        self::$errorTip[strtolower($name)] = $message;
        static::$rules[strtolower($name)] = $callback;
    }

    /**
     * 绑定校验规则到字段
     * @param string $rule
     * @param array|string $field
     * @throws Exception
     * @return $this
     * @return $this
     */
    public function rule($rule, $field)
    {
        $ruleMethod = 'is' . ucfirst($rule);
        if (!isset(static::$rules[$rule]) && !method_exists($this, $ruleMethod)) {
            throw new Exception('_NOT_FOUND_', 'validate rule [' . $rule . ']');
        }

        $params = array_slice(func_get_args(), 2);

        $this->dateBindRule[] = [
            'rule' => $rule,
            'field' => (array)$field,
            'params' => (array)$params
        ];
        return $this;
    }

    /**
     * 批量绑定校验规则到字段
     *
     * @param array $rules
     *
     * @return $this
     */
    public function rules($rules)
    {
        foreach ($rules as $rule => $field) {
            if (is_array($field) && is_array($field[0])) {
                array_unshift($field, $rule);
                call_user_func_array([$this, 'rule'], $field);
            } else {
                $this->rule($rule, $field);
            }
        }
        return $this;
    }

    /**
     * 自定义错误提示信息
     *
     * @param string $msg
     * @return $this
     */
    public function message($msg)
    {
        $this->dateBindRule[count($this->dateBindRule) - 1]['message'] = $msg;

        return $this;
    }

    /**
     * 执行校验并返回布尔值
     *
     * @return boolean
     */
    public function runValidate()
    {
        foreach ($this->dateBindRule as $bind) {
            foreach ($bind['field'] as $field) {
                $values = isset($this->data[$field]) ? $this->data[$field] : null;

                if (isset(static::$rules[$field])) {
                    $callback = static::$rules[$field];
                } else {
                    $callback = [$this, 'is' . ucfirst($bind['rule'])];
                }

                $result = true;
                if ($bind['rule'] == 'arr') {
                    $result = call_user_func($callback, $values, $bind['params'], $field);
                } else {
                    is_array($values) || $values = [$values];
                    if (count($values) == 0) {
                        $result = call_user_func($callback, $values, $bind['params'], $field);
                    } else {
                        foreach ($values as $value) {
                            $result = $result && call_user_func($callback, $value, $bind['params'], $field);
                        }
                    }
                }

                if (!$result) {
                    $this->error($field, $bind);
                }
            }
        }

        return count($this->getErrors()) === 0;
    }

    /**
     * 添加一条错误信息
     *
     * @param string $field
     * @param string $bind
     */
    private function error($field, &$bind)
    {
        $label = (isset($this->label[$field]) && !empty($this->label[$field])) ? $this->label[$field] : $field;
        $this->errorMsg[$field][] = vsprintf(str_replace('{field}', $label, ($bind['message'] ?? '{field} ' . self::$errorTip[strtolower($bind['rule'])])), $bind['params']);
    }

    /**
     * 设置字段显示别名
     *
     * @param string $label
     *
     * @return $this
     */
    public function label($label)
    {
        if (is_array($label)) {
            $this->label = array_merge($this->label, $label);
        } else {
            $this->label[$this->dateBindRule[count($this->dateBindRule) - 1]['field'][0]] = $label;
        }

        return $this;
    }

    /**
     * 获取所有错误信息
     *
     * @param int $format 返回的格式 0返回数组，1返回json,2返回字符串
     * @param string $delimiter format为2时分隔符
     * @return array|string
     */
    public function getErrors($format = 0, $delimiter = '|')
    {
        switch ($format) {
            case 1:
                return json_encode($this->errorMsg, JSON_UNESCAPED_UNICODE);
            case 2:
                $return = '';
                foreach ($this->errorMsg as $val) {
                    $return .= ($return == '' ? '' : $delimiter) . implode($delimiter, $val);
                }
                return $return;
        }
        return $this->errorMsg;
    }

    /**
     * 数据基础验证-是否必须填写的参数
     *
     * @param  string $value 需要验证的值
     *
     * @return bool
     */
    public static function isRequire($value)
    {
        if (null === $value) {
            return false;
        } elseif (is_string($value) && trim($value) === '') {
            return false;
        } elseif (is_array($value) && empty($value)) {
            return false;
        }

        return true;
    }

    /**
     * 数据基础验证-是否为字符串参数
     *
     * @param  string $value 需要验证的值
     *
     * @return bool
     */
    public static function isString($value)
    {
        return is_string($value);
    }

    /**
     * 数据基础验证-是否大于
     *
     * @param int $value 要比较的值
     * @param int $max 要大于的长度
     *
     * @return bool
     */
    public static function isGt($value, $max)
    {
        is_array($max) && $max = $max[0];
        if (!is_numeric($value)) {
            return false;
        } elseif (function_exists('bccomp')) {
            return bccomp($value, $max, 14) == 1;
        }
        return $value > $max;
    }

    /**
     * 数据基础验证-是否小于
     *
     * @param int $value 要比较的值
     * @param int $min 要小于的长度
     *
     * @return bool
     */
    public static function isLt($value, $min)
    {
        is_array($min) && $min = $min[0];
        if (!is_numeric($value)) {
            return false;
        } elseif (function_exists('bccomp')) {
            return bccomp($min, $value, 14) == 1;
        }
        return $value < $min;
    }

    /**
     * 数据基础验证-字符串长度是否大于
     *
     * @param string $value 字符串
     * @param int $max 要大于的长度
     *
     * @return bool
     */
    public static function isLengthGt($value, $max)
    {
        is_array($max) && $max = $max[0];
        return self::isLength($value, $max);
    }

    /**
     * 数据基础验证-字符串长度是否小于
     *
     * @param string $value 字符串
     * @param int $min 要小于的长度
     *
     * @return bool
     */
    public static function isLengthLt($value, $min)
    {
        is_array($min) && $min = $min[0];
        return self::isLength($value, 0, $min);
    }

    public static function isLengthEq($value, $require_length)
    {
        $require_length = $require_length[0];
        $value = trim($value);
        if (!is_string($value)) {
            return false;
        }
        $length = function_exists('mb_strlen') ? mb_strlen($value) : strlen($value);
        if ($length == $require_length) {
            return true;
        }
        return false;
    }

    /**
     * 数据基础验证-判断数据是否在数组中
     *
     * @param string $value 字符串
     * @param array $array 比较的数组
     *
     * @return bool
     */
    public static function isIn($value, $array)
    {
        is_array($array[0]) && $array = $array[0];
        return in_array($value, $array);
    }

    /**
     * 数据基础验证-判断数据是否在数组中
     *
     * @param string $value 字符串
     * @param array $array 比较的数组
     *
     * @return bool
     */
    public static function isNotIn($value, $array)
    {
        is_array($array[0]) && $array = $array[0];
        return !in_array($value, $array);
    }

    /**
     * 数据基础验证-检测字符串长度
     *
     * @param  string $value 需要验证的值
     * @param  int $min 字符串最小长度
     * @param  int $max 字符串最大长度
     *
     * @return bool
     */
    public static function isLength($value, $min = 0, $max = 0)
    {
        $value = trim($value);
        if (!is_string($value)) {
            return false;
        }
        $length = function_exists('mb_strlen') ? mb_strlen($value) : strlen($value);

        if (is_array($min)) {
            $min = $min[0];
            $max = $min[1];
        }
        if ($min != 0 && $length < $min) {
            return false;
        }
        if ($max != 0 && $length > $max) {
            return false;
        }
        return true;
    }

    /**
     * 数据基础验证-是否是空字符串
     *
     * @param  string $value 需要验证的值
     *
     * @return bool
     */
    public static function isEmpty($value)
    {
        if (empty($value)) {
            return true;
        }
        return false;
    }

    /**
     * 数据基础验证-检测数组，数组为空时候也返回false
     *
     * @param  string $value 需要验证的值
     *
     * @return bool
     */
    public static function isArr($value)
    {
        if (!is_array($value) || empty($value)) {
            return false;
        }
        return true;
    }

    /**
     * 数据基础验证-是否是Email 验证：xxx@qq.com
     *
     * @param  string $value 需要验证的值
     *
     * @return bool
     */
    public static function isEmail($value)
    {
        return filter_var($value, \FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * 数据基础验证-是否是IP
     *
     * @param  string $value 需要验证的值
     *
     * @return bool
     */
    public static function isIp($value)
    {
        return filter_var($value, \FILTER_VALIDATE_IP) !== false;
    }

    /**
     * 数据基础验证-是否是数字类型
     *
     * @param  string $value 需要验证的值
     *
     * @return bool
     */
    public static function isNumber($value)
    {
        return is_numeric($value);
    }

    /**
     * 数据基础验证-是否是整型
     *
     * @param  int $value 需要验证的值
     *
     * @return bool
     */
    public static function isInt($value)
    {
        return filter_var($value, \FILTER_VALIDATE_INT) !== false;
    }

    /**
     * 数据基础验证-是否是布尔类型
     *
     * @param  int $value 需要验证的值
     *
     * @return bool
     */
    public static function isBool($value)
    {
        return (is_bool($value)) ? true : false;
    }

    /**
     * 数据基础验证-是否是身份证
     *
     * @param  string $value 需要验证的值
     *
     * @return bool
     */
    public static function isCard($value)
    {
        return preg_match("/^(\d{15}|\d{17}[\dx])$/i", $value);
    }

    /**
     * 数据基础验证-是否是移动电话 验证：1385810XXXX
     *
     * @param  string $value 需要验证的值
     *
     * @return bool
     */
    public static function isMobile($value)
    {
        return preg_match('/^[+86]?1[354678][0-9]{9}$/', trim($value));
    }

    /**
     * 数据基础验证-是否是电话 验证：0571-xxxxxxxx
     *
     * @param  string $value 需要验证的值
     * @return bool
     */
    public static function isPhone($value)
    {
        return preg_match('/^[0-9]{3,4}[\-]?[0-9]{7,8}$/', trim($value));
    }

    /**
     * 数据基础验证-是否是URL 验证：http://www.baidu.com
     *
     * @param  string $value 需要验证的值
     *
     * @return bool
     */
    public static function isUrl($value)
    {
        return filter_var($value, \FILTER_VALIDATE_URL) !== false;
    }

    /**
     * 数据基础验证-是否是邮政编码 验证：311100
     *
     * @param  string $value 需要验证的值
     *
     * @return bool
     */
    public static function isZip($value)
    {
        return preg_match('/^[1-9]\d{5}$/', trim($value));
    }

    /**
     * 数据基础验证-是否是QQ
     *
     * @param  string $value 需要验证的值
     *
     * @return bool
     */
    public static function isQq($value)
    {
        return preg_match('/^[1-9]\d{4,12}$/', trim($value));
    }

    /**
     * 数据基础验证-是否是英文字母
     *
     * @param  string $value 需要验证的值
     *
     * @return bool
     */
    public static function isEnglish($value)
    {
        return preg_match('/^[A-Za-z]+$/', trim($value));
    }

    /**
     * 数据基础验证-是否是中文
     *
     * @param  string $value 需要验证的值
     *
     * @return bool
     */
    public static function isChinese($value)
    {
        return preg_match("/^([\xE4-\xE9][\x80-\xBF][\x80-\xBF])+$/", trim($value));
    }

    /**
     * 检查是否是安全的账号
     *
     * @param string $value
     *
     * @return bool
     */
    public static function isSafeAccount($value)
    {
        return preg_match('/([^\x{4e00}-\x{9fa5}_a-zA-Z0-9]){1,16}/u', $value);
    }

    /**
     * 检查是否是安全的昵称
     *
     * @param string $value
     *
     * @return bool
     */
    public static function isSafeNickname($value)
    {
        return preg_match("/^[-\x{4e00}-\x{9fa5}a-zA-Z0-9_\.]{1,16}$/u", $value);
    }

    /**
     * 检查是否是安全的密码
     *
     * @param string $str
     *
     * @return bool
     */
    public static function isSafePassword($str)
    {
        if (preg_match('/[\x80-\xff]./', $str) || preg_match('/\'|"|\"/', $str) || strlen($str) < 6 || strlen($str) > 20) {
            return false;
        }
        return true;
    }

    /**
     * 检查是否是正确的标识符
     *
     * @param string $value 以字母或下划线开始，后面跟着任何字母，数字或下划线。
     *
     * @return mixed
     */
    public static function isIdentifier($value)
    {
        return preg_match('/^[a-zA-Z_][a-zA-Z0-9_]+$/', trim($value));
    }

    /**
     * 匹配中文、字母、数字、下划线横线
     *
     * @param string $value
     *
     * @return mixed
     */
    public static function isSign($value)
    {
        return preg_match('/([^\x{4e00}-\x{9fa5}_a-zA-Z0-9]){1}/u', $value);
    }
}
