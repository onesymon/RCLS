# 0. FETCH MAIN INTO YOUR LOCAL DEVICE
git status                                    					 # Check if you have changes
git stash push -m "WIP before pull"          			 # Saves current work temporarily
git checkout main
git pull origin main

# 1. SWITCH TO YOUR BRANCH
git stash push -m "WIP before switching branch"
git checkout iyje                         
git stash pop                                					# Restore your stashed changes (if any)

# 1.2 COMMIT YOUR LOCAL CHANGES TO YOUR BRANCH
git add .
git commit -m "dami ko binago"
git push origin iyje                        					# Regular push, no force

# 2.1 MERGE LATEST MAIN INTO YOUR BRANCH 
git checkout iyje
git pull origin main                         					# Merge latest main into your branch

# 2.2 Resolve any conflicts if needed
git add .
git commit -m "Merged latest main into iyje"
git push origin iyje                        			 		# Safe push again

# 3. FINAL MERGE OF BRANCH TO MAIN
git checkout main
git pull origin main                        			 		# Always pull before merge
git merge iyje                               					# Merge your changes from branch
git push origin main                         					# Push merged main branch
