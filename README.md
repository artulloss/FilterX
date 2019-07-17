# FilterX
The next generation chat filter!
# Features
- Invisible filtering - By default the plugin will not make it apparent to players that things they say in chat are filtered, this is to encourage cleanliness in chat as most people won't know what they're saying is censored so they won't try to bypass the filter.
- Soft mute system - This plugin has a configurable infraction system where infractions will be incremented in one of two modes. Mode 1 will add 1 infraction per message with a filtered word and mode 2 will do one infraction per filtered word. You can set the soft mute length based on the amount of infractions in a certain period of time.
- Configurability - Everything is configurable and the configuration is validated with  [this](https://github.com/lezhnev74/pasvl) fabulous library
- Support for SQLite and MySQL via [libasynql](https://github.com/poggit/libasynql)

# Installation

1. Download the latest build from here https://poggit.pmmp.io/ci/artulloss/FilterX/
2. Configure the plugin to your liking. For the database SQLite will work great for 1 server and MySQL will work for multiple servers. If you want to test the filter out I reccomend switching the invisible options off then back on when you're ready to use it, or use a seperate device to confirm if the messages are blocked.
3. If you run into any issues with the plugin open an issue and I'll try to get back to you within several hours.

**Made with ❤️ by Adam**