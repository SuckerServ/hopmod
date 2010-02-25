require "Json"

local VARS_FILE = "log/player_vars"
local MINIMUM_UPDATE_INTERVAL = 1000 * 60 * 60

local variablesByIpAddr = {}
local variablesById = {}

local lastsave = 0

local function checkValue(value)
    if type(value) == "function" or type(value) == "userdata" then error("cannot store value of a " .. type(value) .. " type") end
end

local function saveVars()
    local file = io.open(VARS_FILE, "w")
    if not file then
        server.log_error("Unable to save player vars")
    end
    file:write(Json.Encode(variablesByIpAddr))
    file:close()
end

local function loadVars()
    local file = io.open(VARS_FILE)
    if not file then return end
    variablesByIpAddr = Json.Decode(file:read("*a"))
    file:close()
end

function server.player_vars(cn)
    local id = server.player_id(cn)
    if id == -1 then error("invalid cn") end
    local vars = variablesById[id]
    local publicInterface = {}
    setmetatable(publicInterface, {
        __index = function(_, key)
            return vars
        end,
        __newindex = function()
            error("table is read-only")
        end
    })
    return publicInterface
end

function server.player_create_var(cn, name, value)

    local id = server.player_id(cn)
    if id == -1 then error("invalid cn") end
    local vars = variablesById[id][name]
    if vars[name] then error(string.format("variable %s already exists for player %i", name, id)) end
    checkValue(value)
    vars[name] = value
    
    local controller = {
        set = function(value) checkValue(value); vars[name] = value end,
        unset = function() vars[name] = nil end
    }
    return controller
end

function server.set_iplong_var(iplong, name, value)

    local vars = variablesByIpAddr[iplong]
    if not vars then
        vars = {}
        variablesByIpAddr[iplong] = vars
    end
    vars[name] = value
    
    for _, cn in ipairs(server.clients()) do
        local id = server.player_id(cn)
        if id then
            variablesById[id][name] = variablesById[id][name] or value
        end
    end
end

server.event_handler("connect", function(cn)

    local id = server.player_id(cn)
    variablesById[id] = variablesById[id] or {}
    
    local ipSetVars = variablesByIpAddr[server.player_iplong(cn)]
    if ipSetVars then
        local vars = variablesById[id]
        for key, value in pairs(ipSetVars) do
            vars[key] = vars[key] or value
        end
    end
end)

server.event_handler("renaming", function(cn, futureId)
    local currentId = server.player_id(cn)
    variablesById[futureId] = variablesById[currentId]
    variablesById[currentId] = nil
end)

server.event_handler("disconnect", function()
    if server.playercount == 0 and server.uptime - lastsave > MINIMUM_UPDATE_INTERVAL then
        saveVars()
        lastsave = server.uptime
    end
end)

server.event_handler("started", loadVars)
server.event_handler("shutdown", saveVars)
