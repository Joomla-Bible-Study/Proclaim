# Joomla 6 Native Compatibility Migration Plan

**Branch:** `feature/joomla4-mvc-migration`
**Goal:** Remove all deprecated patterns to enable turning off Joomla's backwards compatibility layer

## Issues to Fix

### 1. Factory::getUser() - DEPRECATED
**Files:** 1
**Replacement:** `Factory::getApplication()->getIdentity()`

| File | Line | Current | Replacement |
|------|------|---------|-------------|
| `site/tmpl/cwmsermon/default.php` | 37 | `Factory::getUser()` | `Factory::getApplication()->getIdentity()` |

### 2. Factory::getDate() - DEPRECATED
**Files:** 19 occurrences
**Replacement:** `new Date()` from `Joomla\CMS\Date\Date`

| File | Line |
|------|------|
| `admin/src/Model/CwmmediafileModel.php` | 753 |
| `admin/src/Model/CwmmessageModel.php` | 666, 671 |
| `admin/src/Model/CwmcommentModel.php` | 309 |
| `admin/src/Model/CwmtemplatecodeModel.php` | 237 |
| `admin/src/Model/CwmlocationModel.php` | 158 |
| `admin/src/Model/CwmteacherModel.php` | 419 |
| `admin/src/Model/CwmtopicModel.php` | 129 |
| `admin/src/Model/CwmserverModel.php` | 401 |
| `admin/src/Model/CwmtemplateModel.php` | 211 |
| `admin/src/Model/CwmpodcastModel.php` | 134 |
| `admin/src/Model/CwmmessagetypeModel.php` | 158 |
| `admin/src/Model/CwmserieModel.php` | 451 |
| `admin/src/Helper/CwmguidedtourHelper.php` | 468 |
| `site/src/Model/CwmsermonsModel.php` | 621 |
| `site/src/Model/CwmsermonModel.php` | 146, 300 |
| `site/src/Helper/Cwmpagebuilder.php` | 331 |
| `site/tmpl/cwmsermon/default_commentsform.php` | 173 |

### 3. CMSObject - DEPRECATED
**Files:** 6
**Replacement:** `\stdClass` or remove if not needed

| File | Line | Usage |
|------|------|-------|
| `admin/src/Model/CwmpodcastModel.php` | 24, 106 | Return type hint |
| `admin/src/View/Cwmcpanel/HtmlView.php` | 50 | Property type |
| `admin/src/View/Cwmbackup/HtmlView.php` | 22, 39, 43 | Use statement, property |
| `admin/src/View/Cwmadmin/HtmlView.php` | 29, 197, 205 | Use statement, docblock |

### 4. Docblock Legacy References
**Files:** ~7
**Fix:** Update `@see JController` and "JView class" / "JModel class" comments

| File | Line | Current | Replacement |
|------|------|---------|-------------|
| `admin/src/Model/CwmtemplatesModel.php` | 45 | `@see JController` | `@see BaseController` |
| `site/src/Model/CwmlandingpageModel.php` | 40 | `@see JController` | `@see BaseController` |
| `site/src/Model/CwmsermonsModel.php` | 56 | `@see JController` | `@see BaseController` |
| `site/src/Model/CwmseriesdisplaysModel.php` | 44 | `@see JController` | `@see BaseController` |
| `admin/src/View/Cwmpodcast/HtmlView.php` | 4 | `JView html` | `HtmlView` |
| `admin/src/View/Cwmserie/HtmlView.php` | 29 | `JView class` | `HtmlView class` |
| `admin/src/Model/CwmcpanelModel.php` | 22 | `JModel class` | `Model class` |
| `site/src/Model/CwmteacherModel.php` | 106 | Commented `JInput` | Remove comment |

## Implementation Order

1. Fix `Factory::getUser()` (1 file)
2. Fix `Factory::getDate()` (add `use Joomla\CMS\Date\Date;` and replace calls)
3. Fix `CMSObject` usage (replace with appropriate alternatives)
4. Fix docblock references

## Testing

After fixes:
- Run `composer check` to verify code style
- Run `composer test` to verify unit tests pass
- Test in Joomla 6 with backwards compatibility disabled

## Completion

- [x] All Factory::getUser() replaced
- [x] All Factory::getDate() replaced
- [x] All CMSObject usage removed
- [x] All legacy docblocks updated
- [x] Tests passing (335 tests, 1120 assertions)
- [x] PR created: https://github.com/Joomla-Bible-Study/Proclaim/pull/new/feature/joomla4-mvc-migration
- [ ] PR merged
