require "geoip"

local BANNER_DELAY = 1000
local show_country_message = server.show_country_message == 1

local function sendServerBanner(cn)
    
    if server.player_vars(cn).shown_banner then return end
    
    local sid = server.player_sessionid(cn)
    
    server.sleep(BANNER_DELAY, function()

        -- cancel if not the same player from 1 second ago
        if sid ~= server.player_sessionid(cn) then return end
        
        server.player_msg(cn, server.motd)
        server.player_vars(cn).shown_banner = true
    end)
end

local function onConnect(cn)

    local country = geoip.ip_to_country(server.player_ip(cn))
    
    if show_country_message and #country > 0 then
        
        local connect_info = string.format("%s connected from %s.", green(server.player_displayname(cn)), green(country))
        local extra_info = " (IP: " .. green(server.player_ip(cn)) .. ")"
        
        for _, cn in ipairs(server.clients()) do
            
            if server.player_priv_code(cn) == server.PRIV_ADMIN then
                connect_info = connect_info .. extra_info
            end
            
            server.player_msg(cn, connect_info)
        end
    end
end

server.event_handler("connect",onConnect)
server.event_handler("maploaded", sendServerBanner)
server.event_handler("disconnect", function(cn) server.player_vars(cn).shown_banner = nil end)
