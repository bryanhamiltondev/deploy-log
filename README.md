# deploy-log

One commit per production deploy. Every entry is a real patch that ran on a live
site: what changed, what almost went wrong, and what the verify step saw.

No backdating, no bulk imports. If nothing shipped today, no commit today -
that's the point of a log.

## Format

One `LOG.md` entry per deploy:

```
## 2026-11-11 - worker-name (site)
- Change: one line, what the user sees
- Risk: what could have gone wrong
- Verify: how we know it landed (served HTML, not disk)
- Tag: worker tag kept as .bak on the server
```

## Why commit this

1. The deploy log IS the commit history. If git is quiet, the work is invisible
   - even when the work shipped.
2. Forcing one paragraph per deploy catches what a chat thread hides: the
   pattern that repeated, the guard that saved the day.
3. Raw material. Every entry is a future article that writes itself.

Built around the [guarded-deploy-workers](https://github.com/bryanhamiltondev/guarded-deploy-workers)
pattern: token-gated, self-deleting PHP patch workers.
