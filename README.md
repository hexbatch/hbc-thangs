# Hexbatch thangs , an improvement on things

Designed for the hexbatch code, this is a laravel library that runs code from a tree, doing the leafs first.

Hooks can be added to any node, to run before or after. The hooks that run before can alter the data used by the leaf.
The leafs add data to their parents. If a leaf fails, the parent fails. Exceptions can be stored, or thown.

Designed to run in memory only, or use storage when running as async jobs.

Trees can have any combination of nodes, and depth, that are sync, async, saved, and not.

Has a builder class!
