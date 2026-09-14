# Maintaining open accounting sessions

This guide explains how to review, close, or delete open RADIUS accounting records from the daloRADIUS operators interface.

> **Warning:** An open accounting record is not necessarily stale. The maintenance page does not check NAS connectivity or user inactivity. A session may still be active when you preview or change it.

## Open the maintenance page

Log in to the operators interface and go to:

```text
Accounting → Maintenance → Open-session maintenance
```

Access depends on the permissions assigned to the operator account.

The separate **Delete accounting history (all records)** page is not covered by this guide. It can remove closed accounting history as well as open records and should not be confused with open-session maintenance.

## Which records are eligible?

Open-session maintenance considers only accounting records whose `acctstoptime` is either:

- `NULL`; or
- the legacy zero value `0000-00-00 00:00:00`.

It does not use `acctupdatetime`, `acctsessiontime`, traffic counters, or NAS reachability to decide whether a record is stale.

You can select eligible records with one of two scopes:

| Scope | Matches |
|---|---|
| **Username** | Open records for the supplied username. Matching follows the accounting database collation. Wildcards are not supported. |
| **Session start before date** | Open records whose `acctstarttime` is strictly earlier than `00:00:00` on the selected date, using the accounting database/server timezone. |

For example, selecting `2026-09-01` includes sessions started before `2026-09-01 00:00:00`. It does not include sessions started during September 1, and it is not a last-activity cutoff.

## Choose an action

| Action | Effect | Accounting data |
|---|---|---|
| **Close sessions** | Sets `acctstoptime` to the current database time and `acctterminatecause` to `Admin-Reset`. | Keeps the record, recorded duration, and traffic counters. |
| **Delete records** | Permanently removes the selected open rows from the accounting table. | Deletes the recorded session and its usage data. |

### Close sessions

Use **Close sessions** when you need to clear an open accounting record while preserving its existing accounting data.

Closing a record:

- does not send a Disconnect-Request or CoA packet to the NAS;
- does not disconnect the user;
- does not recalculate `acctsessiontime`, input octets, or output octets;
- may prevent a later Interim-Update or Stop packet from matching the record as an open session.

If the user may still be connected, verify the session on the NAS before closing it. Use the appropriate NAS disconnect workflow when an actual network disconnection is required.

### Delete records

Use **Delete records** only when the open rows themselves must be removed and losing their accounting data is acceptable.

Deletion is irreversible through daloRADIUS. It can change reports, usage totals, billing inputs, and audit history. Back up the accounting database before a large or sensitive cleanup.

For environments where accounting accuracy matters, prefer closing a verified stale record over deleting it.

## Preview and confirm a maintenance operation

1. Select **Close sessions** or **Delete records**.
2. Select a scope.
3. Enter an exact username or a date in `YYYY-MM-DD` format.
4. Click **Preview matching records**.
5. Review the username, NAS address, session start time, recorded update time, duration, and traffic counters.
6. Expand **Preview details** if you need the full batch and concurrency information.
7. Click **Close N sessions** or **Permanently delete N records** only after verifying the displayed rows.

No accounting record is changed during the preview step.

## Preview safety rules

A preview is a bounded, one-use snapshot:

- at most 100 records are included, ordered by accounting ID;
- only the displayed records can be changed;
- the confirmation expires after 10 minutes;
- the confirmation can be submitted only once;
- a record changed by an Interim-Update, Stop packet, or another process after the preview is skipped;
- a newly matching record is never added to an already approved operation;
- changing the action, scope, username, or date requires a new preview.

If more than 100 records match, complete the first batch, create another preview, and review the next batch before confirming it.

## Understand the result

After confirmation, daloRADIUS reports three counts:

| Result | Meaning |
|---|---|
| **Affected** | Records successfully closed or deleted. |
| **Skipped** | Records that changed after the preview or were no longer eligible. |
| **Failed** | Records for which the database operation failed. |

Skipped records are a safety mechanism, not necessarily an error. Preview again to inspect their current state. For failed records, review the daloRADIUS and database logs before retrying.

## Operational recommendations

- Verify that a session is actually stale before changing it.
- Start with a username scope or a narrow date range.
- Review counters and timestamps rather than relying only on the open status.
- Prefer **Close sessions** when accounting history must be retained.
- Reserve **Delete records** for confirmed unwanted data.
- Do not use this page as an inactivity detector or assume that it disconnects users.
- Do not automate blind deletion merely to clear the online-users view.
- Back up the accounting database before bulk cleanup.

## Related issue

The distinction between closing and deleting accounting records is discussed in [GitHub issue #668](https://github.com/lirantal/daloradius/issues/668).
