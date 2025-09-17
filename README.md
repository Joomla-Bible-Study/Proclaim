CWM Proclaim
==================

Status
-----------
| Branch      | Version | Release Date | Joomla Version | PHP Minimum                                                                    |
|-------------|---------|--------------|----------------|--------------------------------------------------------------------------------|
| Development | 10.0.x  |              | 4+             | [![PHP](https://img.shields.io/badge/PHP-V8.1.0-green)](https://www.php.net/)  |
| Main        | 10.0.1  | Sep 10, 2025 | 4+             | [![PHP](https://img.shields.io/badge/PHP-V8.1.0-green)](https://www.php.net/)  |

*NOTE:* The main branch will always reflect the current, released stable version. Only bug fixes and minor updates should be applied to the main branch. New features are to be introduced into the development branch only.

Overview
--------
Proclaim is a Joomla!® component written by a team of web servants to further the teaching of God's Word. The component displays information about your church's Bible Studies or sermons in a wide variety of ways. Proclaim is flexible, customizable, and powerful. Easy-to-configure templates give you the maximum amount of choices. Show only what you want in whatever way you want.

Embed YouTube videos, play audio, show study notes—even create your own HTML display pages. You can have multiple locations, series, podcasting, and sharing with social media sites. Please see the example pages to discover just some of what Bible Study can do for your church - and the best part is that the component is completely free. Support is top-notch and also free. Bottom line: we want to help you spread the gospel.

Contributing
------------
We appreciate contributions in various capacities. Below are some ways that you can contribute to this project.

### Setup
1. [Fork this repository.][fork]
2. Load dev dependencies with [Composer][composer]: `php composer.phar install --dev`
3. [Set up your dev environment][setup]

### Development
1. [Create a topic branch.][branch]
2. Implement your feature or bug fix.
3. If you implemented a new feature or added extra functionality, create/update unit tests for that feature
4. Run `bin/phing build`
5. If not building successfully, go back to step **1**
6. Add your files to the repository: `git add .`
7. Commit your files: `git commit -m "Implemented feature [x]"`
8. Push your changes: `git push`
9. [Submit a pull request][pr]

Please **make sure to make specific contributions** when submitting pull requests. For example, if fixing bugs across multiple features of the component, create a branch for each fix, and submit a separate pull request for each fix separately, instead of fixing everything in `main`, and then just trying to pull your `main` branch into `Joomla-Bible-Study:main`.


### Translation
The language files periodically need to be updated as the component matures. To submit changes ot add new languages, follow the same procedures as above to submit a [pull][pr] request.

### Testing
For every major release, we prefer to have an approximate 2-week testing window. If you would like to help in testing and giving us feedback on the most recent versions of the component, let us know

[fork]: http://help.github.com/fork-a-repo/
[branch]: http://learn.github.com/p/branching.html
[pr]: http://help.github.com/send-pull-requests/
[phing]: http://www.phing.info/
[setup]: https://github.com/Joomla-Bible-Study/Proclaim/wiki/Setting-up-your-development-environment
<!-- @IGNORE PREVIOUS: link -->
[composer]: https://getcomposer.org/download/

Reporting Issues and Requesting Features
----------------------------------------
Use the **Issues** section for reporting bugs or requesting features. Please make sure that when bugs are reported, you include steps to reproduce them.