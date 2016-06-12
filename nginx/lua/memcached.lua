local memcached = require "resty.memcached"

local key = ngx.var.arg_key

local index = ngx.crc32_long(key) % #config["memcached"] + 1

local host = config["memcached"][index]["host"]
local port = config["memcached"][index]["port"]

local memc, err = memcached:new()

if not memc then
    ngx.log(ngx.ERR, err)
    ngx.exit(ngx.HTTP_SERVICE_UNAVAILABLE)
end

memc:set_timeout(100)

local ok, err = memc:connect(host, port)

if not ok then
    ngx.log(ngx.ERR, err)
    ngx.exit(ngx.HTTP_SERVICE_UNAVAILABLE)
end

local method = ngx.req.get_method()

if method == "GET" then
    local res, flags, err = memc:get(key)

    if err then
        ngx.log(ngx.ERR, err)
        ngx.exit(ngx.HTTP_SERVICE_UNAVAILABLE)
    end

    ngx.print(res)
elseif method == "PUT" then
    local value = ngx.req.get_body_data()
    local ttl = ngx.var.arg_ttl

    local ok, err = memc:set(key, value, ttl)

    if not ok then
        ngx.log(ngx.ERR, err)
        ngx.exit(ngx.HTTP_SERVICE_UNAVAILABLE)
    end
else
    ngx.exit(ngx.HTTP_NOT_ALLOWED)
end

memc:set_keepalive(1000, 10)