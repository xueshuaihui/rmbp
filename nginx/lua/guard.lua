local uri = string.match(ngx.var.request_uri, "[^?]+")
local args = ngx.req.get_uri_args()

for _, rule in ipairs(config["ruleset"]) do
    local pattern = rule["pattern"]
    local option = rule["option"]
    local single = rule["single"]

    if ngx.re.match(uri, pattern, option or "") then
        ngx.var.skip = "1"

        local ttl = rule["ttl"]

        if ttl then
            ngx.var.ttl = ttl
        end


        local fields = rule["fields"]

        if single then
            if table.getn(args) > 0 then
                ngx.var.skip = "1"
                fields = nil
            end
        end

        if fields then
            for name in pairs(args) do
                if fields[name] then
                    if fields[name] == "number" then
                        if not tonumber(args[name]) then
                            ngx.exit(OK)
                        end
                    end
                else
                    args[name] = nil
                end
            end
        end

        local key = {
            ngx.var.request_method, " ",
            ngx.var.scheme, "://",
            ngx.var.host, uri,
        }

        args = ngx.encode_args(args);

        if args ~= "" then
            key[#key + 1] = "?"
            key[#key + 1] = args
        end

        key = table.concat(key)
        key = ngx.md5(key)

        ngx.var.key = key

        break
    end
end