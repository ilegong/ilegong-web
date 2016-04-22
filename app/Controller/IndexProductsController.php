<?php

class IndexProductsController extends AppController
{
    var $uses =
        array('WeshareProduct', 'Weshare', 'WeshareAddress', 'Order', 'Cart', 'User', 'OrderConsignees', 'Oauthbind', 'SharedOffer', 'CouponItem',
            'SharerShipOption', 'WeshareShipSetting', 'OfflineStore', 'Comment', 'RebateTrackLog', 'ProxyRebatePercent', 'ShareUserBind', 'UserSubReason', 'ShareFavourableConfig', 'ShareAuthority');

    var $components = array('Weixin', 'WeshareBuy', 'Buying', 'RedPacket', 'ShareUtil', 'ShareAuthority', 'OrderExpress', 'PintuanHelper', 'RedisQueue', 'DeliveryTemplate', 'Orders', 'Weshares', 'UserFans');

    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->layout = null;
    }

    public function tutorial()
    {
        $this->layout = null;
    }

    /**
     * api
     */
    public function index_products($tag = 0)
    {
        $this->layout = null;
        $index_products = $this->ShareUtil->index_products($tag);

        echo json_encode($index_products);
        exit();
    }

    /**
     * api
     */
    public function recent_orders_and_creators($share_id)
    {
        $this->layout = null;

        $orders_and_creators = $this->ShareUtil->recent_orders_and_creators($share_id);

        echo json_encode($orders_and_creators);
        exit();
    }
}
