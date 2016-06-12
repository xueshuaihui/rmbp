config = {}

config["memcached"] = {
    { host = "127.0.0.1", port = "11211" },
}

config["ruleset"] = {
    --{ pattern = "^/+$", ttl = 1800, single = "1" },
    { pattern = "/project", ttl = 1800 },
    { pattern = "/help", ttl = 3600 },
    { pattern = "/article", ttl = 86400 },
}
