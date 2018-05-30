HOW TO RELEASE
==============

### 1. Update Documents

Update `CHANGES.md` and `CHANGES.ja.md`. Update `README.md` and `README.ja.md`,
too, if necessary.


### 2. Update Version

[Packagist][1] (which this library is registered into) refers to git tags.
To utilize the mechanism, create a new tag for a new version. See
[Versions and constraints][2] for details.

    $ git tag X.Y.Z
    $ git push origin X.Y.Z


### 3. Publish Library

If [GitHub Service Hook][3] is working correctly, changes are automatically
detected by [Packagist][1].


### 4. Update API Reference

The following command updates documents under `docs` folder.

    $ rm -rf docs
    $ phpdoc


### 5. Publish API Reference

    $ git add docs
    $ git commit -m 'Updated API reference for version X.Y.Z.'
    $ git push


See [Configuring a publishing source for GitHub Pages][4] for details.


[1]: https://packagist.org
[2]: https://getcomposer.org/doc/articles/versions.md
[3]: https://packagist.org/about#how-to-update-packages
[4]: https://help.github.com/articles/configuring-a-publishing-source-for-github-pages/
