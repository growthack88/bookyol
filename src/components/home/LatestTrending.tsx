import { latestBooks, trendingNow } from '@/data/mockData';
import { useInView } from '@/hooks/useInView';
import { Clock, TrendingUp, ArrowLeft } from 'lucide-react';

const LatestTrending = () => {
  const [sectionRef, isInView] = useInView<HTMLElement>({ threshold: 0.1 });

  return (
    <section
      ref={sectionRef}
      className={`py-12 transition-all duration-700 ${
        isInView ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-8'
      }`}
    >
      <div className="container mx-auto px-4">
        <div className="grid gap-6 md:grid-cols-2">
          {/* Latest Added */}
          <div className="rounded-2xl border border-border bg-card p-6">
            <div className="mb-4 flex items-center gap-2">
              <div className="flex h-8 w-8 items-center justify-center rounded-lg bg-primary/10 text-primary">
                <Clock className="h-4 w-4" />
              </div>
              <h3 className="text-lg font-semibold text-foreground">أُضيفت حديثًا</h3>
            </div>
            <ul className="space-y-3">
              {latestBooks.map((book, index) => (
                <li key={index}>
                  <button className="group flex w-full items-center justify-between rounded-lg px-3 py-2 text-right transition-colors hover:bg-accent">
                    <div>
                      <span className="font-medium text-foreground group-hover:text-primary transition-colors">
                        {book.title}
                      </span>
                      <span className="mx-2 text-muted-foreground">—</span>
                      <span className="text-sm text-muted-foreground">{book.author}</span>
                    </div>
                    <ArrowLeft className="h-4 w-4 text-muted-foreground opacity-0 transition-opacity group-hover:opacity-100" />
                  </button>
                </li>
              ))}
            </ul>
          </div>

          {/* Trending Searches */}
          <div className="rounded-2xl border border-border bg-card p-6">
            <div className="mb-4 flex items-center gap-2">
              <div className="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-100 text-amber-600">
                <TrendingUp className="h-4 w-4" />
              </div>
              <h3 className="text-lg font-semibold text-foreground">رائج الآن</h3>
            </div>
            <ul className="space-y-3">
              {trendingNow.map((term, index) => (
                <li key={index}>
                  <button className="group flex w-full items-center justify-between rounded-lg px-3 py-2 text-right transition-colors hover:bg-accent">
                    <div className="flex items-center gap-3">
                      <span className="flex h-6 w-6 items-center justify-center rounded-full bg-muted text-xs font-medium text-muted-foreground">
                        {index + 1}
                      </span>
                      <span className="font-medium text-foreground group-hover:text-primary transition-colors">
                        {term}
                      </span>
                    </div>
                    <ArrowLeft className="h-4 w-4 text-muted-foreground opacity-0 transition-opacity group-hover:opacity-100" />
                  </button>
                </li>
              ))}
            </ul>
          </div>
        </div>
      </div>
    </section>
  );
};

export default LatestTrending;
