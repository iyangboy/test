<?php

namespace App\Http\Controllers;

use App\Model\ProjectInfo;
use App\Model\UserInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WfxZhtController extends Controller
{
    //手机号码验证
    protected function isMobile($mobile)
    {
        if (!is_numeric($mobile)) {
            return false;
        }
        return preg_match('#^13[\d]{9}$|^14[5,7]{1}\d{8}$|^15[^4]{1}\d{8}$|^17[0,6,7,8]{1}\d{8}$|^18[\d]{9}$#', $mobile) ? true : false;
    }

    //手机号唯一验证
    protected function isOnly($tablename, $phonenum)
    {

        $interval_time = 60 * 60;
        $time = time();
        $add_time = $time - $interval_time;
        $curnum = DB::select('select * from ' . $tablename . ' where phonenum =' . $phonenum . ' and addtime>' . $add_time);
//        $curnum = M($tablename)->where($wh)->find();
        if ($curnum) {
            return false;
        } else {
            return true;
        }
    }

    protected function addModel($table_name, $data)
    {
        $k = '';
        $val = '';
        foreach ($data as $key => $ls) {
            $k = $k . $key . ',';
            $val = $val . '"' . $ls . '"' . ',';
        }
        $k = rtrim($k, ",");
        $val = rtrim($val, ",");

//        print_r('insert into ' . $table_name . ' (' . $k . ') values (' . $val . ')');
//        exit;
        $rs = DB::insert('insert into ' . $table_name . ' (' . $k . ') values (' . $val . ')');
        return $rs;
    }

    //HTML前端页面展示
    public function showHtml($id)
    {
        $user_info = ProjectInfo::where('project', $id)->first();
        if (!$user_info) {
            print_r('对不起，访问的页面不存在，请核实项目名称');
            exit;
        } else {
            if ($_POST) {
                $table_name = $user_info->table_name;
//                print_r($table_name);exit;
                $phonenum = isset($_POST['phonenum']) ? $_POST['phonenum'] : '';
//                if (!($this->isMobile($phonenum))) {
//                    return '不是正确的手机号码';
//                }
                $isok = $this->isOnly($table_name, $phonenum);
                if ($isok) {
                    unset($_POST['_token']);

                    $data = $_POST;
                    $data['addtime'] = time();
                    $result = $this->addModel($table_name, $data);
                    if ($result) {
                        return '预约成功，我们的工作人员会很快联系您的';
                    } else {
                        return '数据错误,请电话联系客服哦';
                    }
                } else {
                    return '很抱歉，该号码已提交过';
//                    return '很抱歉，该号码限时提交';
                }
            } else {
                $html = $user_info->project;
                $classify = $user_info->classify;
                $table_name = $user_info->table_name;
                $totals = 0;
                if (empty(!$table_name)) {
                    $totals = $this->idTotal($table_name);
                }
                if ($totals > 0) {
                    $total = $totals;
                } else {
                    $total = 0;
                }
                if (empty(!$classify)) {
                    return view("wfx_zht.html.$classify.$html", compact('total'));
                } else {
                    $test = 1;
                    return view("wfx_zht.html.$html", compact('test'));
                }
            }
        }
    }

    //获取数据总数
    protected function idTotal($tablename)
    {
        $count = DB::select('select COUNT("*") from ' . $tablename);
        $t1 = $count[0];
        $array = json_decode(json_encode($t1), TRUE);
        $total = $array['COUNT("*")'];
        return $total;
    }

}
