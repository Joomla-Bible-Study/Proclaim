A patch release fixing a permissions problem introduced in 10.5.8. If you use Proclaim's per-section permissions, or if the Assets screen has been showing large numbers in the **Parent Drifted** column, this one matters.

## The Assets screen was reporting healthy records as broken

Proclaim's Permissions → Assets screen lists, per table, how many records inherit their permissions and how many carry rules of their own. Since 10.5.8 it has also been reporting almost all of them under **Parent Drifted**, alongside a row of **Section permission records** marked as needing cleanup.

Nothing was actually wrong with those records. The screen was checking them against the wrong expectation: it asked whether each record's permissions hang directly off Proclaim, when since 10.5.8 they correctly hang off their section — Messages, Teachers, Media Files and so on. Everything filed correctly was reported as misfiled.

The giveaway, if you saw it, was that the Parent Drifted number matched the Custom Rules number exactly on every row.

## ⚠️ Clean Up was undoing your per-section permissions

This is the part worth reading if you have pressed that button.

Because every record looked misfiled, **Clean Up** set about "repairing" them. It deleted the section permission records — the ones that hold your per-section settings — and moved every record's permissions back onto Proclaim itself. A rule set on, say, Messages then had nothing left to apply to.

It did this quietly, and it could run during an update as well as from the button.

Clean Up now leaves correctly filed records alone, keeps your section permission records, and repairs only what is genuinely misfiled.

## If your permissions were already flattened

Updating restores the section permission records by itself. Records that were moved off them are put back the first time you run **Clean Up** on the Assets screen, or the migration wizard — it is not done automatically, so nothing changes underneath you without being asked for.

Your per-section settings themselves are not recoverable if the records holding them were deleted, so it is worth checking Permissions after updating and re-applying anything that has gone.

## Requirements

Joomla 5.4 or later, PHP 8.3 or later. Unchanged.
