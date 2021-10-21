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

````yml
routeEnhancers:
  AliasExample:
    type: Simple
    routePath: '/address/{partneralias}'
    _arguments:
      partneralias: addressid
    aspects:
      partneralias:
        type: UniqueAlias
        tableName: 'tt_address'
        aliasField: 'company'
        expires: '15d'
        uniqueConfiguration:
          fallbackCharacter: '-'
````       

In the partial ListItem.html of `tt_address` the link could be generated this way:
`<f:link.page pageUid="{settings.singlePid}" additionalParams="{addressid: address}">Details</f:link.page>`.

With the Unique Alias Mapper, the URL will look like this: `https://example.com/my/page/address/burger-king-germany/`

With TYPO3's Core "PersistedAliasMapper" the URL will look like this `https://example.com/my/page/address/Burger%20King%20Germany/`.

On top, this Mapper comes with a caching layer in between, just like RealURL's "uniqAlias" feature did in the past.

Even more complex route enhancers are possible too. An example for a link with a controller/action (Movie/show) of an
extension `myext` where the uid is in the parameter `tx_myext_pi1[content]` and `title` is a column 
of `tx_myext_domain_model_content`:

````yml
routeEnhancers:
  MyextPlugin:
    type: Extbase
    limitToPages:
      - 24
    extension: Myext
    plugin: Pi1
    routes:
      -
        routePath: '/entry/{myext_title}'
        _controller: 'Movie::show'
        _arguments:
          myext_title: content
    defaultController: 'Movie::list'
    aspects:
      myext_title:
        type: UniqueAlias
        tableName: tx_myext_domain_model_content
        aliasField: title
        expires: 15d
        uniqueConfiguration:
          fallbackCharacter: '-'
````  

## ToDo

The expiration functionality does not cut it yet, so we need to improve this area. Pull Requests welcome.

## Thanks

Thanks to the RealURL authors for providing such a good logic, which served as inspiration for this Mapper. On top, the creators of the Aspects/Mappers - thanks to them for providing such a flexible and extensible way for creating custom mappers and enhancers.

## License

As TYPO3 Core, _uniquealiasmapper_ is licensed under GPL2 or later. See the LICENSE file for more details.

## Authors & Maintenance

_uniquealiasmapper_ was initially created for a customer project by Benni Mack for [b13, Stuttgart](https://b13.com).

[Find more TYPO3 extensions we have developed](https://b13.com/useful-typo3-extensions-from-b13-to-you) that help us deliver value in client projects. As part of the way we work, we focus on testing and best practices to ensure long-term performance, reliability, and results in all our code.
