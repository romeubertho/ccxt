<?php

namespace ccxt;

// PLEASE DO NOT EDIT THIS FILE, IT IS GENERATED AND WILL BE OVERWRITTEN:
// https://github.com/ccxt/ccxt/blob/master/CONTRIBUTING.md#how-to-contribute-code

use Exception as Exception; // a common import

class coinex extends Exchange {

    public function describe () {
        return array_replace_recursive (parent::describe (), array (
            'id' => 'coinex',
            'name' => 'CoinEx',
            'version' => 'v1',
            'countries' => 'CN',
            'rateLimit' => 1000,
            'has' => array (
                'fetchTickers' => true,
                'fetchOHLCV' => true,
                'fetchOrder' => true,
                'fetchOpenOrders' => true,
                'fetchClosedOrders' => true,
                'fetchMyTrades' => true,
            ),
            'timeframes' => array (
                '1m' => '1min',
                '3m' => '3min',
                '5m' => '5min',
                '15m' => '15min',
                '30m' => '30min',
                '1h' => '1hour',
                '2h' => '2hour',
                '4h' => '4hour',
                '6h' => '6hour',
                '12h' => '12hour',
                '1d' => '1day',
                '3d' => '3day',
                '1w' => '1week',
            ),
            'urls' => array (
                'logo' => 'https://user-images.githubusercontent.com/1294454/38046312-0b450aac-32c8-11e8-99ab-bc6b136b6cc7.jpg',
                'api' => array (
                    'public' => 'https://api.coinex.com',
                    'private' => 'https://api.coinex.com',
                    'web' => 'https://www.coinex.com',
                ),
                'www' => 'https://www.coinex.com',
                'doc' => 'https://github.com/coinexcom/coinex_exchange_api/wiki',
                'fees' => 'https://www.coinex.com/fees',
            ),
            'api' => array (
                'web' => array (
                    'get' => array (
                        'res/market',
                    ),
                ),
                'public' => array (
                    'get' => array (
                        'market/list',
                        'market/ticker',
                        'market/ticker/all',
                        'market/depth',
                        'market/deals',
                        'market/kline',
                    ),
                ),
                'private' => array (
                    'get' => array (
                        'balance',
                        'order',
                        'order/pending',
                        'order/finished',
                        'order/finished/{id}',
                        'order/user/deals',
                    ),
                    'post' => array (
                        'order/limit',
                        'order/market',
                    ),
                    'delete' => array (
                        'order/pending',
                    ),
                ),
            ),
            'fees' => array (
                'trading' => array (
                    'maker' => 0.0,
                    'taker' => 0.001,
                ),
                'funding' => array (
                    'withdraw' => array (
                        'BCH' => 0.0,
                        'BTC' => 0.001,
                        'LTC' => 0.001,
                        'ETH' => 0.001,
                        'ZEC' => 0.0001,
                        'DASH' => 0.0001,
                    ),
                ),
            ),
            'limits' => array (
                'amount' => array (
                    'min' => 0.001,
                    'max' => null,
                ),
            ),
            'precision' => array (
                'amount' => 8,
                'price' => 8,
            ),
        ));
    }

    public function fetch_markets () {
        $response = $this->webGetResMarket ();
        $markets = $response['data']['market_info'];
        $result = array ();
        $keys = is_array ($markets) ? array_keys ($markets) : array ();
        for ($i = 0; $i < count ($keys); $i++) {
            $key = $keys[$i];
            $market = $markets[$key];
            $id = $market['market'];
            $quoteId = $market['buy_asset_type'];
            $baseId = $market['sell_asset_type'];
            $base = $this->common_currency_code($baseId);
            $quote = $this->common_currency_code($quoteId);
            $symbol = $base . '/' . $quote;
            $precision = array (
                'amount' => $market['sell_asset_type_places'],
                'price' => $market['buy_asset_type_places'],
            );
            $numMergeLevels = is_array ($market['merge']) ? count ($market['merge']) : 0;
            $result[] = array (
                'id' => $id,
                'symbol' => $symbol,
                'base' => $base,
                'quote' => $quote,
                'baseId' => $baseId,
                'quoteId' => $quoteId,
                'active' => true,
                'taker' => floatval ($market['taker_fee_rate']),
                'maker' => floatval ($market['maker_fee_rate']),
                'info' => $market,
                'precision' => $precision,
                'limits' => array (
                    'amount' => array (
                        'min' => floatval ($market['least_amount']),
                        'max' => null,
                    ),
                    'price' => array (
                        'min' => floatval ($market['merge'][$numMergeLevels - 1]),
                        'max' => null,
                    ),
                ),
            );
        }
        return $result;
    }

    public function parse_ticker ($ticker, $market = null) {
        $timestamp = $ticker['date'];
        $symbol = $market['symbol'];
        $ticker = $ticker['ticker'];
        $last = floatval ($ticker['last']);
        return array (
            'symbol' => $symbol,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'high' => floatval ($ticker['high']),
            'low' => floatval ($ticker['low']),
            'bid' => floatval ($ticker['buy']),
            'bidVolume' => null,
            'ask' => floatval ($ticker['sell']),
            'askVolume' => null,
            'vwap' => null,
            'open' => null,
            'close' => $last,
            'last' => $last,
            'previousClose' => null,
            'change' => null,
            'percentage' => null,
            'average' => null,
            'baseVolume' => floatval ($ticker['vol']),
            'quoteVolume' => null,
            'info' => $ticker,
        );
    }

    public function fetch_ticker ($symbol, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $response = $this->publicGetMarketTicker (array_merge (array (
            'market' => $market['id'],
        ), $params));
        return $this->parse_ticker($response['data'], $market);
    }

    public function fetch_tickers ($symbols = null, $params = array ()) {
        $this->load_markets();
        $response = $this->publicGetMarketTickerAll ($params);
        $data = $response['data'];
        $timestamp = $data['date'];
        $tickers = $data['ticker'];
        $ids = is_array ($tickers) ? array_keys ($tickers) : array ();
        $result = array ();
        for ($i = 0; $i < count ($ids); $i++) {
            $id = $ids[$i];
            $market = $this->markets_by_id[$id];
            $symbol = $market['symbol'];
            $ticker = array (
                'date' => $timestamp,
                'ticker' => $tickers[$id],
            );
            $result[$symbol] = $this->parse_ticker($ticker, $market);
        }
        return $result;
    }

    public function fetch_order_book ($symbol, $params = array ()) {
        $this->load_markets();
        $response = $this->publicGetMarketDepth (array_merge (array (
            'market' => $this->market_id($symbol),
            'merge' => '0.00000001',
        ), $params));
        return $this->parse_order_book($response['data']);
    }

    public function parse_trade ($trade, $market = null) {
        $timestamp = $this->safe_integer($trade, 'create_time');
        $tradeId = $this->safe_string($trade, 'id');
        $orderId = $this->safe_string($trade, 'id');
        if (!$timestamp) {
            $timestamp = $trade['date'];
            $orderId = null;
        } else {
            $tradeId = null;
        }
        $timestamp *= 1000;
        $price = floatval ($trade['price']);
        $amount = floatval ($trade['amount']);
        $symbol = $market['symbol'];
        $cost = $this->safe_float($trade, 'deal_money');
        if (!$cost)
            $cost = floatval ($this->cost_to_precision($symbol, $price * $amount));
        $fee = $this->safe_float($trade, 'fee');
        return array (
            'info' => $trade,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'symbol' => $symbol,
            'id' => $tradeId,
            'order' => $orderId,
            'type' => 'limit',
            'side' => $trade['type'],
            'price' => $price,
            'amount' => $amount,
            'cost' => $cost,
            'fee' => $fee,
        );
    }

    public function fetch_trades ($symbol, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $response = $this->publicGetMarketDeals (array_merge (array (
            'market' => $market['id'],
        ), $params));
        return $this->parse_trades($response['data'], $market, $since, $limit);
    }

    public function parse_ohlcv ($ohlcv, $market = null, $timeframe = '5m', $since = null, $limit = null) {
        return [
            $ohlcv[0],
            floatval ($ohlcv[1]),
            floatval ($ohlcv[3]),
            floatval ($ohlcv[4]),
            floatval ($ohlcv[2]),
            floatval ($ohlcv[5]),
        ];
    }

    public function fetch_ohlcv ($symbol, $timeframe = '5m', $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $response = $this->publicGetMarketKline (array_merge (array (
            'market' => $market['id'],
            'type' => $this->timeframes[$timeframe],
        ), $params));
        return $this->parse_ohlcvs($response['data'], $market, $timeframe, $since, $limit);
    }

    public function fetch_balance ($params = array ()) {
        $this->load_markets();
        $response = $this->privateGetBalance ($params);
        $result = array ( 'info' => $response );
        $balances = $response['data'];
        $currencies = is_array ($balances) ? array_keys ($balances) : array ();
        for ($i = 0; $i < count ($currencies); $i++) {
            $id = $currencies[$i];
            $balance = $balances[$id];
            $currency = $this->common_currency_code($id);
            $account = array (
                'free' => floatval ($balance['available']),
                'used' => floatval ($balance['frozen']),
                'total' => 0.0,
            );
            $account['total'] = $this->sum ($account['free'], $account['used']);
            $result[$currency] = $account;
        }
        return $this->parse_balance($result);
    }

    public function parse_order ($order, $market = null) {
        // TODO => check if it's actually milliseconds, since examples were in seconds
        $timestamp = $this->safe_integer($order, 'create_time') * 1000;
        $price = floatval ($order['price']);
        $cost = $this->safe_float($order, 'deal_money');
        $amount = $this->safe_float($order, 'amount');
        $filled = $this->safe_float($order, 'deal_amount');
        $symbol = $market['symbol'];
        $remaining = $this->amount_to_precision($symbol, $amount - $filled);
        $status = $order['status'];
        if ($status === 'done') {
            $status = 'closed';
        } else {
            // not_deal
            // part_deal
            $status = 'open';
        }
        return array (
            'id' => $this->safe_string($order, 'id'),
            'datetime' => $this->iso8601 ($timestamp),
            'timestamp' => $timestamp,
            'status' => $status,
            'symbol' => $symbol,
            'type' => $order['order_type'],
            'side' => $order['type'],
            'price' => $price,
            'cost' => $cost,
            'amount' => $amount,
            'filled' => $filled,
            'remaining' => $remaining,
            'trades' => null,
            'fee' => array (
                'currency' => $market['quote'],
                'cost' => floatval ($order['deal_fee']),
            ),
            'info' => $order,
        );
    }

    public function create_order ($symbol, $type, $side, $amount, $price = null, $params = array ()) {
        $this->load_markets();
        $method = 'privatePostOrder' . $this->capitalize ($type);
        $market = $this->market ($symbol);
        $amount = floatval ($amount);
        $request = array (
            'market' => $market['id'],
            'amount' => $this->amount_to_precision($symbol, $amount),
            'type' => $side,
        );
        if ($type === 'limit') {
            $price = floatval ($price);
            $request['price'] = $this->price_to_precision($symbol, $price);
        }
        $response = $this->$method (array_merge ($request, $params));
        $order = $this->parse_order($response['data'], $market);
        $id = $order['id'];
        $this->orders[$id] = $order;
        return $order;
    }

    public function cancel_order ($id, $symbol = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $response = $this->privateDeleteOrderPending (array_merge (array (
            'id' => $id,
            'market' => $market['id'],
        ), $params));
        return $this->parse_order($response['data'], $market);
    }

    public function fetch_order ($id, $symbol = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $response = $this->privateGetOrder (array_merge (array (
            'id' => $id,
            'market' => $market['id'],
        ), $params));
        return $this->parse_order($response['data'], $market);
    }

    public function fetch_open_orders ($symbol = null, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $request = array (
            'market' => $market['id'],
        );
        if ($limit)
            $request['limit'] = $limit;
        $response = $this->privateGetOrderPending (array_merge ($request, $params));
        return $this->parse_orders($response['data']['data'], $market);
    }

    public function fetch_closed_orders ($symbol = null, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $request = array (
            'market' => $market['id'],
        );
        if ($limit)
            $request['limit'] = $limit;
        $response = $this->privateGetOrderFinished (array_merge ($request, $params));
        return $this->parse_orders($response['data']['data'], $market);
    }

    public function fetch_my_trades ($symbol = null, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $response = $this->privateGetOrderUserDeals (array_merge (array (
            'market' => $market['id'],
            'page' => 1,
            'limit' => 100,
        ), $params));
        return $this->parse_trades($response['data']['data'], $market, $since, $limit);
    }

    public function nonce () {
        return $this->milliseconds ();
    }

    public function sign ($path, $api = 'public', $method = 'GET', $params = array (), $headers = null, $body = null) {
        $path = $this->implode_params($path, $params);
        $url = $this->urls['api'][$api] . '/' . $this->version . '/' . $path;
        $query = $this->omit ($params, $this->extract_params($path));
        if ($api === 'public') {
            if ($query)
                $url .= '?' . $this->urlencode ($query);
        } else if ($api === 'web') {
            $url = $this->urls['api'][$api] . '/' . $path;
            if ($query)
                $url .= '?' . $this->urlencode ($query);
        } else {
            $this->check_required_credentials();
            $nonce = $this->nonce ();
            $query = array_merge (array (
                'access_id' => $this->apiKey,
                'tonce' => (string) $nonce,
            ), $query);
            $query = $this->keysort ($query);
            $urlencoded = $this->urlencode ($query);
            $signature = $this->hash ($this->encode ($urlencoded . '&secret_key=' . $this->secret));
            $headers = array (
                'Authorization' => strtoupper ($signature),
                'Content-Type' => 'application/json',
            );
            if (($method === 'GET') || ($method === 'DELETE')) {
                $url .= '?' . $urlencoded;
            } else {
                $body = $this->json ($query);
            }
        }
        return array ( 'url' => $url, 'method' => $method, 'body' => $body, 'headers' => $headers );
    }

    public function request ($path, $api = 'public', $method = 'GET', $params = array (), $headers = null, $body = null) {
        $response = $this->fetch2 ($path, $api, $method, $params, $headers, $body);
        $code = $this->safe_string($response, 'code');
        $data = $this->safe_value($response, 'data');
        if ($code !== '0' || !$data) {
            $responseCodes = array (
                '24' => '\\ccxt\\AuthenticationError',
                '25' => '\\ccxt\\AuthenticationError',
                '107' => '\\ccxt\\InsufficientFunds',
                '600' => '\\ccxt\\OrderNotFound',
                '601' => '\\ccxt\\InvalidOrder',
                '602' => '\\ccxt\\InvalidOrder',
                '606' => '\\ccxt\\InvalidOrder',
            );
            $ErrorClass = $this->safe_value($responseCodes, $code, '\\ccxt\\ExchangeError');
            throw new $ErrorClass ($response['message']);
        }
        return $response;
    }
}
