# BBC\iPlayerRadio\Resolver

A simple requirement resolution system for plain-old-php objects.

[![Build Status](https://travis-ci.org/bbc/ipr-php-resolver.svg?branch=master)](https://travis-ci.org/bbc/ipr-php-resolver)
[![Latest Stable Version](https://poser.pugx.org/bbc/ipr-resolver/v/stable.svg)](https://packagist.org/packages/bbc/ipr-resolver)
[![Total Downloads](https://poser.pugx.org/bbc/ipr-resolver/downloads.svg)](https://packagist.org/packages/bbc/ipr-resolver)
[![License](https://poser.pugx.org/bbc/ipr-resolver/license.svg)](https://packagist.org/packages/bbc/ipr-resolver)

- [Installation](#installation)
- [Background](#background)
- [Usage](#usage)
- [How this actually works](#how-this-actually-works)
- [Credits](#credits)

## Installation

This is a standard composer library, so usual install rules apply:

```php
$ composer require bbc/ipr-resolver
```

This library requires **PHP 5.5 or higher** as it makes use of
[generators](http://php.net/manual/en/language.generators.overview.php)! 

## Background

Objects often have dependencies on each other. It's a pain. To use a uniquely radio example; we sometimes put "Brand"
objects on our pages (think "The Archers"). However, we have decided that the most useful thing to show to the audience
is the latest episode from that brand. That doesn't come as part of the brand metadata, so we have to do a separate call
for it. We end up with something like this:

```php
$brand = $this->fetchBrand('The Archers');
$brand->loadLatestEpisode();

$twig->renderBrand($brand);
```

This gets worse if you have a list of brands and need to loop through each of them, hydrating them with their latest
episode since the work is now probably happening in series and definitely cluttering up your code:

```php
foreach ($brands as $brand) {
    // do this 50 times.
    $brand->loadLatestEpisode();
}
```

And what about if the latest episode ALSO has to make a data call to make itself whole! This is a nightmare. And how
do I even inject dependencies down that chain?!

Wouldn't it be nice if we could just make a brand object, and say; "Sort yourself out bucko!" and it does.
Yeah, we thought so too.

Enter stage left; the Resolver.

## Usage

To flag that an object has dependencies, you need to implement the `BBC\iPlayerRadio\Resolver\HasRequirements` interface
which has a single method: `requires()`.

```php
use BBC\iPlayerRadio\Resolver\HasRequirements;

class Brand implements HasRequirements
{
    ...
    public function requires(array $flags = [])
    {
        $episodes = (yield new LatestEpisodesForProgramme($this->getPID(), 3));
        $this->latestEpisodes = ($episodes)? $episodes : [];
    }
    ...
}
```

If you've never come across [coroutines](https://wiki.php.net/rfc/generators#sending_values) before, this probably
looks a bit weird! But you can think of it like a lazy promise, the yield throws up to the Resolver saying "I need this
to continue, handle it!".

This is how you then grab a fully fleshed out item:

```php
use BBC\iPlayerRadio\Resolver\Resolver;

$resolver = new Resolver();
$resolver->addBackend(new EpisodesBackend);

$brand1 = new Brand(['id' => 'p865ddf6']);
$brand2 = new Brand(['id' => 'pabcdefs']);

$resolver->resolve([$brand1, $brand2]);

// $brand1 and $brand2 are now fully hydrated with their latest episodes in the  $this->latestEpisodes variable.
```

Hang on, what's that `new EpisodesBackend`? This is how the Resolver knows how to solve requirements. Resolver backends
take requirements and generate the actual result of the requirement.

So the EpisodesBackend would look something like this:

```php
class EpisodesBackend implements BBC\iPlayerRadio\Resolver\ResolverBackend
{
    /**
     * Returns whether this backend can handle a given Requirement. Requirements
     * can be absolutely anything, so make sure to verify correctly against it.
     *
     * @param   mixed   $requirement
     * @return  bool
     */
    public function canResolve($requirement)
    {
        return $requirement instanceof LatestEpisodesForProgramme;
    }

    /**
     * Given a list of requirements, perform their resolutions. Requirements can
     * be absolutely anything from strings to full-bore objects.
     *
     * @param   array   $requirements
     * @return  array
     */
    public function doResolve(array $requirements)
    {
        $results = [];
        foreach ($requirements as $req) {
            if ($req instanceof LatestEpisodesForProgramme) {
                $results = $this->fetchEpisodesForProgramme($req->id, $req->limit);
            }
        }
        return $results;
    }
}

```

Whilst the syntax of using the resolver is slightly nicer, how does this help performance? We're still looping
through and running each query synchronously.

Well, consider that the fetchLatestEpisodes was making a cURL request. We could now batch all those up do it as
a single multi-curl request:

```php
public function doResolve(array $requirements)
{
    $results = [];
    $urls = [];
    foreach ($requirements as $req) {
        if ($req instanceof LatestEpisodesForProgramme) {
            $urls[] = $req->getDataURL();
        }
    }
    
    // Now kick off a multi-curl request for all those URLs:
    $ch = curl_multi_init();
    ...
    
    return $results;
}
```

Using the Resolver allows you to ignore the actual details of how your objects fetch their data, and instead simply
define what they *need* and let the Resolver and ResolverBackends do the work!

> **Hint hint** imagine coupling this with the [WebserviceKit](http://github.com/bbc/ipr-php-webservicekit) library
> with a ResolverBackend that looks for QueryInterface instances and then multiFetch()'s them... ;)

You can also pass flags into the Resolver to help `requires()` functions work out what they need to do:

```php
class Brand implements HasRequirements
{
    public function requires(array $flags = [])
    {
        if (in_array('WITH_ATTRIBUTION', $flags)) {
            $attribution = (yield new WithAttribution($this->parent));
        }
    }
}
```

```php
$resolver->resolve($brand, ['WITH_ATTRIBUTION']);
```

**Note**: all `requires()` functions see all flags, so keep them specific to avoid problems!

If a requirement is not supported by any backend (none of the `canResolve()` functions return true), then a
`BBC\iPlayerRadio\Resolver\UnresolvableRequirementException` will be thrown, contained within it the requirement
that failed, which you can access with `getFailedRequirement()`.

## Resolver Backends

Resolver Backends have two functions; firstly to state whether or not they understand a requirement (via the `canResolve`
function) and then to take a list of requirements that it can resolve and to resolve them (via `doResolve`)!

There are some rules for writing your own resolver backends:

- Be as specific and safe in `canResolve` as you can. Be sure that you can actually resolve it
- Keep backends generic. Have a "CURLResolverBackend" rather than individual "EpisodeResolver", "BrandResolver" etc. Resolver
 backends should be "resolution strategy" based rather than data model based. The more generic the better.
- The requirements passed in `doRequire` can be in any order and from multiple objects. Treat them that way!
- **Always** return the results in the same order as the requirements! Weird stuff will happen if you don't!

## How this actually works

You might be wondering how the whole `yield` thing works. It's a feature in PHP called
[Generators](http://php.net/manual/en/language.generators.overview.php). Usually when people think of Generators
they think of the "cheap iterator" side of things, but there's another side to Generators in a feature called
"Coroutines".

This basically involves exploiting the fact that PHP suspends execution of a function when it reaches a yield, only
returning control when the loop advances. By calling a function that yield's outside of a loop, you get an object
which you can manually advance, allowing you to effectively hold a function in suspended animation until you have
a real value for it.

Mind melting no? Here's some really good links that helped me get my head around them:

- The [original PHP RFC](https://wiki.php.net/rfc/generators#sending_values) that details generators and coroutines
- [Javascript, but explains the theory well](http://jlongster.com/A-Study-on-Solving-Callbacks-with-JavaScript-Generators)
- [JS again, but also good](http://colintoh.com/blog/staying-sane-with-asynchronous-programming-promises-and-generators)

## Credits

This library is based off of an extremely similar technique showcased by [Bastian Hofmann](https://twitter.com/BastianHofmann)
in his talk at the PHPUK 2015 conference. Thanks Bastian!
