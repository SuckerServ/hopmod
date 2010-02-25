extern "C"{
#include <lua.h>
#include <lualib.h>
#include <lauxlib.h>
}
#include <GeoIP.h>

static GeoIP * geoip = NULL;

static int load_database(lua_State * L)
{
    const char * filename = luaL_checkstring(L, 1);
    if(geoip) GeoIP_delete(geoip);
    geoip = GeoIP_open(filename, GEOIP_STANDARD | GEOIP_MEMORY_CACHE);
    lua_pushboolean(L, geoip != NULL);
    return 1;
}

static int ip_to_country(lua_State * L)
{
    if(!geoip) return luaL_error(L, "missing GeoIP database");
    const char * ipaddr = luaL_checkstring(L, 1);
    const char * country = GeoIP_country_name_by_addr(geoip, ipaddr); 
    lua_pushstring(L, (country ? country : ""));
    return 1;
}

static int ip_to_country_code(lua_State * L)
{
    if(!geoip) return luaL_error(L, "missing GeoIP database");
    const char * ipaddr = luaL_checkstring(L, 1);
    const char * code = GeoIP_country_code_by_addr(geoip, ipaddr);
    lua_pushstring(L, (code ? code : ""));
    return 1;
}

namespace lua{
namespace module{

void open_geoip(lua_State * L)
{
    static luaL_Reg functions[] = {
        {"load_database", load_database},
        {"ip_to_country", ip_to_country},
        {"ip_to_country_code", ip_to_country_code},
        {NULL, NULL}
    };
    
    luaL_register(L, "geoip", functions);
}

} //namespace module
} //namespace lua
