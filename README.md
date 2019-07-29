## Unique Alias Mapper for URL Routing in TYPO3 v9+

TYPO3 v9 comes with great ways to enhance URLs, called "Enhancers" for additions / suffixes for a page URL, and "Aspects" (and their special category "Mappers"), to transform a specific value back and forth between the application and the URL.

This extension ships with a new Mapper called `UniqueAlias`. It works similar to what people know from the `PersistedAliasMapper`, however when using TYPO3 installations, our projects mostly want:
- to have the key unique, even if two records have the same title
- everything special-chared and lower-case

## Installation

Use it via `composer req b13/uniquealiasmapper` or install the Extension `uniquealiasmapper` from the TYPO3 Extension Repository.

Once ready, you can configure the Mapper in your site configuration file.


## Example with tt_address.company

Example which maps `$_GET['addressid']` into a `/address/burger-king-germany` based on
the `company` field of `tt_address`.

    routeEnhancers:
      AliasExample:
        type: Simple
        routePath: '/address/{partneralias}'
        _arguments:
          addressid: partneralias
        aspects:
          partneralias:
            type: UniqueAlias
            tableName: 'tt_address'
            aliasField: 'company'
            expires: '15d'
            uniqueConfiguration:
              fallbackCharacter: '-'
              

With the Unique Alias Mapper, the URL will look like this: `https://example.com/my/page/address/burger-king-germany/`

With TYPO3's Core "PersistedAliasMapper" the URL will look like this `https://example.com/my/page/address/Burger%20King%20Germany/`.

On top, this Mapper comes with a caching layer in between, just like RealURL's "uniqAlias" feature did in the past.

## ToDo

The expiration functionality does not cut it yet, so we need to improve this area. Pull Requests welcome.

## Thanks

Thanks to the RealURL authors for providing such a good logic, which served as inspiration for this Mapper. On top, the creators of the Aspects/Mappers - thanks to them for providing such a flexible and extensible way for creating custom mappers and enhancers.

## Authors & Maintenance

Benni Mack for b13, Germany.

## License

GPLv2+