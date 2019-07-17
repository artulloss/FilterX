# FilterX
The next generation chat filter!
# Features
- Invisible filtering - By default the plugin will not make it apparent to players that things they say in chat are filtered, this is to encourage cleanliness in chat as most people won't know what they're saying is censored so they won't try to bypass the filter.
- Soft mute system - This plugin has a configurable infraction system where infractions will be incremented in one of two modes. Mode 1 will add 1 infraction per line with a filtered word and mode 2 will do one infraction per filtered word. You can set the soft mute length based on the amount of infractions in a certain period of time.
- Configurability - Everything is configurable and the configuration is validated with  [this](https://github.com/lezhnev74/pasvl) fabulous library
- Support for SQLite and MySQL via [libasynql](https://github.com/poggit/libasynql)
