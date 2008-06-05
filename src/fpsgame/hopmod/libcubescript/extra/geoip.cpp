#include <cubescript.hpp>
#include <GeoIP.h>
#include <boost/bind.hpp>

class geoip_module:public cubescript::module
{
public:
    geoip_module()
    {
        m_geoip=GeoIP_open("GeoIP.dat",GEOIP_STANDARD);
    }
    
    void register_symbols(cubescript::module::domain_accessor * domain)
    {
        typedef cubescript::function1<std::string,const std::string &> country_function;
        static cubescript::module::symbol_delegate<country_function> func_country(this,country_function(boost::bind(&geoip_module::get_country,this,_1)));
        domain->register_symbol("country",&func_country);
    }
private:
    std::string get_country(const std::string & ipaddr)
    {
        if(!m_geoip) throw cubescript::error_key("runtime.plugin.geoip.db_closed");
        const char * country=GeoIP_country_name_by_addr(m_geoip,ipaddr.c_str());
        if(!country) return "unknown";
        return country;
    }
    GeoIP * m_geoip;
};

IMPLEMENT_CUBESCRIPT_MODULE(geoip_module);
