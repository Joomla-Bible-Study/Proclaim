A patch release fixing two problems introduced by 10.6.0. If you have installed 10.6.0, this is worth taking.

## The "servers waiting to be migrated" notice would not go away

If Proclaim told you that servers were waiting to be migrated, migrating them did not clear the message. Neither did unpublishing or deleting the servers it was pointing at. The notice appeared on the dashboard, in System Health, and at the end of a restore, and it stayed there.

The count was looking at the wrong thing. When Proclaim finishes migrating a server it retires it, but the record keeps its original type — so the count went on including every server you had already dealt with. It could only ever have reached zero if you deleted the records outright.

It now counts servers that are genuinely still waiting. Migrating them clears the notice, and servers you have trashed or unpublished yourself are no longer counted against you.

If you have been looking at that message since updating to 10.6.0 and could not work out what was left to do: the answer may well be nothing, and this release will say so.

## The Assets screen showed a spinner that never finished

Opening Permissions → Assets showed a loading spinner and left it there. Pressing **Refresh** loaded the table, and it behaved from then on, so the problem only appeared on the first visit after an update or a new login.

Nothing was actually loading. The screen was waiting for information it had never asked for. It now requests it when you open the screen, which is what the spinner was always claiming.

## Requirements

Joomla 5.4 or later, PHP 8.3 or later. Unchanged from 10.6.0.
