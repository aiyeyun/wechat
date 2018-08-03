<?php
/**
 * Created by PhpStorm.
 * User: wang.haibo
 * Date: 2018/8/3
 * Time: 16:08
 */

namespace library\utils;


class TskeyUtil
{

    /* 未出库 */
    const STATUS_UNSHELF = 0;

    /* 未使用 */
    const STATUS_UNUSED = 1;

    /* 已使用 */
    const STATUS_USED = 2;

    /* 已解绑 */
    const STATUS_UNWRAP = 3;

    /* 已转移企业版 */
    const STATUS_TRANSFER = 4;

    /* 已作废 */
    const STATUS_INVALID = 9;

}