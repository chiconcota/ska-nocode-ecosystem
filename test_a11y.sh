#!/bin/bash
for file in $(grep -rl 'material-symbols-outlined' ska-builder-core/src/); do
  if ! grep -q 'aria-hidden="true"' "$file"; then
    echo "Missing aria-hidden in: $file"
  fi
done
