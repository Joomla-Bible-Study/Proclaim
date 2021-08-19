CWM Proclaim
==================

Status
-----------
|                                                                          Code                                                                          |   Branch    | Version | Release Date | Joomla Version |
|--------------------------------------------------------------------------------------------------------------------------------------------------------|-------------|---------|--------------|----------------|
| [![](https://travis-ci.com/Joomla-Bible-Study/Joomla-Bible-Study.svg?branch=development)](https://travis-ci.com/Joomla-Bible-Study/Joomla-Bible-Study) | Development | 9.3.0   | TBD          | 3.8+           |
| [![](https://travis-ci.com/Joomla-Bible-Study/Joomla-Bible-Study.svg?branch=master)](https://travis-ci.com/Joomla-Bible-Study/Joomla-Bible-Study)      | Master      | 9.2.6   | April 6, 2021 | 3.8+           |

*NOTE:* The master branch will always reflect the current, released stable version. Only bug fixes and minor updates should be applied to the master branch. New features are to be introduced into the development branch only.

Overview
--------
Proclaim is a Joomla!® component written by a team of web servants to further the teaching of God's Word. The component displays information about your church's Bible Studies or sermons in a wide variety of ways. Proclaim is flexible, customizable, and powerful. Easy to configure templates give you the maximum amount of choices. Show only what you want in whatever way you want.

Embed YouTube videos, play audio, show study notes - even create your own html display pages. You can have multiple locations, series, podcasting, and sharing with social media sites. Please see the example pages for just some of what Bible Study can do for your church - and the best part is the component is completely free. Support is top notch, and also free. Bottom line: we want to help you spread the gospel.

Contributing
------------
We appreciate contributions in various capacities, below are some ways that you can contribute to this project

### Setup
1. [Fork this repository.][fork]
2. Load dev dependencies with [Composer][composer]: `php composer.phar install --dev`
3. [Set up your dev environment][setup]

### Development
1. [Create a topic branch.][branch]
2. Implement your feature or bug fix.
3. If you implemented a new feature or added an extra functionality, create/update unit tests for that feature
4. Run `bin/phing build`
5. If not building successfully, go back to step **1**
6. Add your files to repository: `git add .`
7. Commit your files: `git commit -m "Implemented feature [x]"`
8. Push your changes: `git push`
9. [Submit a pull request][pr]

Please **make sure to make specific contributions** when submitting pull requests. For example, if fixing bugs across multiple features of the component, create a branch for each fix, and submit a separate pull request for each fix separately, instead of fixing everything in `master`, and then just trying to pull your `master` branch into `Joomla-Bible-Study:master`.


### Translation 
The language files periodically need to be updated as the component matures. To submit changes ot add new languages, follow the same procedures as above in order to submit a [pull][pr] request.

### Testing
For every major release, we prefer to have an approximate 2 week testing window. If you would like to help in testing and giving us feedback on the most recent versions of the component, let us know

[fork]: http://help.github.com/fork-a-repo/
[branch]: http://learn.github.com/p/branching.html
[pr]: http://help.github.com/send-pull-requests/
[phing]: http://www.phing.info/
[setup]: https://github.com/Joomla-Bible-Study/Joomla-Bible-Study/wiki/Setting-up-your-development-environment
<!-- @IGNORE PREVIOUS: link -->
[composer]: https://getcomposer.org/download/

Reporting Issues and requesting features
----------------------------------------
Use the **Issues** section for reporting bugs, or requesting features. Please make sure that when bugs are reported, you include steps to reproduce them.
