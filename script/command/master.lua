--[[
    A player command to raise privilege to master
]]

local domains = table_unique(server.parse_list(server["master_domains"]))

if not domains then
    server.log_error("master command: no domains set")
    return
end

local function init() end
local function unload() end

local function run(cn)

    local sid = server.player_sessionid(cn)

    for _, domain in pairs(domains) do
        auth.send_request(cn, master_domain, function(cn, user_id, domain, status)

            if not (sid == server.player_sessionid(cn)) or not (status == auth.request_status.SUCCESS) then
                return
            end
            
            server.setmaster(cn)
            
            server.msg(server.player_displayname(cn) .. " claimed master as '" .. magenta(user_id) .. "'")
            server.log(string.format("%s playing as %s(%i) used auth to claim master.", user_id, server.player_name(cn), cn))
        end)
    end
end

return {init = init, run = run, unload = unload}

