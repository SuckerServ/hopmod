--[[--------------------------------------------------------------------------
--
--    A script to kick spectators
--    who reach the spectate time limit and the server is full
--
--]]--------------------------------------------------------------------------

local max_spec_time = server.spectating_limit


local function unset_mem(cn)

    server.player_vars(cn).spectating_time = nil
    server.player_vars(cn).spectating_last_chat = nil
end

local function unset_mem_all()

    for p in server.gspectators()
    do
	unset_mem(p.cn)
    end
end

local function kick(cn)

    unset_mem(cn)
    server.disconnect(cn, server.DISC_TIMEOUT, "too long inactive in spec")
end

local function chat(cn)

    if server.player_status_code(cn) == server.SPECTATOR
    then
	server.player_vars(cn).spectating_last_chat = server.player_connection_time(cn)
    end
end

local function check_spec_times()

    for p in server.gspectators()
    do
	if ((p:vars().spectating_time or 0) > max_spec_time) and ((p:vars().spectating_last_chat or 0) < (p:connection_time() - 300000))	-- 5 minutes ago
	then
	    kick(p.cn)
	end
    end
end


server.event_handler("disconnect", unset_mem)

server.event_handler("spectator", function(cn, joined)

    if joined == 0
    then
	unset_mem(cn)
    end
end)

server.event_handler("finishedgame", function()

    if (server.mastermode == 0) or (server.mastermode == 1)
    then
	local gamemillis = server.gamemillis
	
	for p in server.gspectators()
	do
	    p:vars().spectating_time = (p:vars().spectating_time or 0) + gamemillis - (p:timeplayed() * 1000)
	end
	
	if server.playercount >= server.maxclients
	then
	    check_spec_times()
	end
    end
end)

server.event_handler("text", chat)

server.event_handler("sayteam", chat)


return {unload = unset_mem_all}
