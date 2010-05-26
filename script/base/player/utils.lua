function server.name_to_cn(name)

    if not name then
        return
    end
    
    name = string.lower(name)

    local full_matches = 0
    local first_matching_cn
    
    for _, cn in pairs(server.clients()) do
    
        if string.lower(server.player_name(cn)) == name then
        
            first_matching_cn = first_matching_cn or cn
            full_matches = full_matches + 1 
        end
    end
    
    if full_matches == 1 then
    
        return first_matching_cn
        
    elseif full_matches == 0 then
    
        local substring_matches = {}
        
        for _, cn in pairs(server.clients()) do
            
            local candidate = server.player_name(cn)
            
            if string.find(string.lower(candidate), name) then
                table.insert(substring_matches, {name=candidate,cn=cn})
            end
        end
        
        if #substring_matches == 1 then
            return substring_matches[1].cn
        end
        
        return nil, substring_matches
        
    elseif full_matches > 1 then
        return nil, full_matches
    end
    
end

function server.disambiguate_name_list(cn, name)

	local message = ""

	name = string.lower(name)

	for p in server.gplayers() do
		local pname = p:name()

		if name == string.lower(pname) then
			message = message .. string.format("%i %s\n", p.cn, pname)
		end
	end

	server.player_msg(cn, message)

end

function server.similar_name_list(cn, names)

	local message = ""

	for i, player in pairs(names) do
		message = message .. string.format("%i %s\n", player.cn, player.name)
	end

	server.player_msg(cn, message)

end

function server.name_to_cn_list_matches(cn,name)

    local lcn, info = server.name_to_cn(name)

    if not lcn then

        if type(info) == "number" then  -- Multiple name matches

            server.player_msg(cn, red(string.format("There are %i players here matching that name:", info)))
        server.disambiguate_name_list(cn,name)

        elseif #info == 0 then  -- no matches

            server.player_msg(cn, red("There are no players found matching that name."))

        else    -- Similar matches

            server.player_msg(cn, red("There are no players found matching that name, but here are some similar names:"))
            server.similar_name_list(cn, info)
        end
        
        return nil
    else
        return lcn
    end
end

function server.group_players(arg1,arg2,arg3)

    if not arg1 then
        return -1
    end

    local tag = nil
    local team = nil

    if arg1 == "all" then
	if not arg2 then
            return -1
	end

	tag = arg2

	if arg3 then
            team = arg3
	else
            team = tag
        end

	for j,cn in ipairs(server.spectators()) do
            if string.find(server.player_name(cn),tag) then
                server.unspec(cn)
            end
        end
    else
        tag = arg1

	if arg2 then
            team = arg2
        else
            team = tag
        end
    end

    for p in server.gplayers() do
        if string.find(p:name(),tag) then
            p:changeteam(team)
        end
    end
    
end


function server.is_bot(cn)
    return cn > 127
end

function server.is_teamkill(player1, player2)
    if not gamemodeinfo.teams then return false end
    if server.player_team(player1) == server.player_team(player2) then return true end
    return false
end

function server.all_players()
	local output = server.players()
	for _, cn in ipairs(server.spectators()) do
        table.insert(output, cn)
	end
	return output
end

function server.valid_cn(cn)
    return server.player_id(tonumber(cn) or -1) ~= -1
end

function server.specall()
    for _,cn in ipairs(server.players()) do server.spec(cn) end
end

function server.unspecall()
    for _,cn in ipairs(server.spectators()) do server.unspec(cn) end
end

function print_displaynamelist(clientnums)
    local names = {}
    for _, cn in ipairs(clientnums) do
        table.insert(names, server.player_displayname(cn))
    end
    return print_list(unpack(names))
end
